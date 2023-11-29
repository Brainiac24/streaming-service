<?php

namespace App\Services\Stream;

use App\Constants\CacheKeys;
use App\Constants\ImagePlaceholders;
use App\Constants\StreamStatuses;
use App\Exceptions\ValidationException;
use App\Models\Event;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Mailing\MailingRepository;
use App\Repositories\Stream\StreamRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Helper\HelperService;
use App\Services\Image\ImageService;
use App\Services\Mailing\MailingService;
use App\Services\Nimble\NimbleService;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Str;

class StreamService
{

    public function __construct(
        public StreamRepository $streamRepository,
        public EventSessionRepository $eventSessionRepository,
        public NimbleService $nimbleService,
        public ImageService $imageService,
        public MailingRepository $mailingRepository,
        public MailingService $mailingService
    ) {
    }


    public function create(Event $event, $eventSessionData)
    {
        $key = Str::lower(Str::random(8));
        $nimbleConfig = config('services.nimble');
        $nimbleData = $this->nimbleService->createStream($key);

        $data["input"] = [
            "rtmp_url" => $nimbleConfig["transcoder_default_url"],
            "rtmp_key" => $key,
            "rtmp_sharedkey" => Str::lower(Str::random(8))
        ];
        $data["output"] = $nimbleData["output"];
        $data["user_id"] = Auth::id();
        $data["stream_status_id"] = StreamStatuses::NEW;
        $data["title"] = "Плеер";
        $data["key"] = $key;
        $data["is_dvr_enabled"] = true;
        $data["is_dvr_out_enabled"] = true;
        $streamDate = $eventSessionData['start_at'] ?? $event->start_at ?? now();

        if (isset($eventSessionData['parent_id'])) {
            $stream = $this->streamRepository->findByIdForCurrentAuthedUserByEventSessionId($eventSessionData['parent_id']);
            $streamStartAt = Carbon::parse($stream->start_at)->timestamp;

            $checkStreamDates = $this->checkDates($streamStartAt, $streamDate);

            if (!$checkStreamDates['status']) {
                throw new ValidationException(__('Validation error: Please provide correct date period! ' . trim($checkStreamDates['message'] ?? '')));
            }

            CacheServiceFacade::tags([
                CacheKeys::eventSessionIdTag($eventSessionData['parent_id'])
            ])
                ->flush();
        }

        $data["start_at"] = Carbon::parse($streamDate)->format('Y-m-d H:i:s');

        $stream = $this->streamRepository->create($data);

        return $stream;
    }

    public function findById($id)
    {
        return $this->streamRepository->findByIdForCurrentAuthedUser($id);
    }

    public function update($data, $id)
    {
        $stream = $this->streamRepository->findByIdForCurrentAuthedUserWithFares($id);

        if (isset($data['is_fullhd_enabled'])) {
            if ($stream['is_fullhd_enabled'] != $data['is_fullhd_enabled'] && !empty($stream['onair_at'])) {
                throw new ValidationException(__('Validation error: Cannot change FullHD mode when stream was already started!'));
            }
            if ($data['is_fullhd_enabled'] == true) {
                $fares_config_json = json_decode($stream->fares_config_json);
                if (!$fares_config_json->is_fullhd_enabled) {
                    throw new ValidationException(__('Validation error: Current fare do not allow to enable FullHD mode!'));
                }
            }
        }

        $streamStartAt = Carbon::parse($stream->start_at)->timestamp;

        if (isset($data['start_at']) && $streamStartAt != $data['start_at'] && !empty($stream['onair_at'])) {
            throw new ValidationException(__('Validation error: Cannot change stream date when stream was already started!'));
        }

        $checkStreamDates = $this->checkStreamDates($stream, $data);

        if (
            isset($data['is_substream_date_changes_confirmed']) &&
            $data['is_substream_date_changes_confirmed'] &&
            isset($checkStreamDates['streams']) &&
            isset($data['start_at']) &&
            !empty($data['start_at'])
        ) {
            $dateDiff = $data['start_at'] - Carbon::parse($stream->start_at)->timestamp;
            foreach ($checkStreamDates['streams'] as $subStream) {
                $newDate = Carbon::parse($subStream->start_at)->timestamp + $dateDiff;
                $subStream->start_at = Carbon::createFromTimestamp($newDate)->format("Y-m-d H:i:s");
                $subStream->save();
            }
        } else if (!$checkStreamDates['status']) {
            $stream = $stream->toArray();
            $checkStreamDates['streams'] ??= [];
            array_walk_recursive($stream, HelperService::class . '::arrayRecursiveChangeDateFormat');
            array_walk_recursive($checkStreamDates['streams'], HelperService::class . '::arrayRecursiveChangeDateFormat');

            throw new ValidationException(
                __('Validation error: Please provide correct date period! ' . ($checkStreamDates['message'] ?? '') . 'Or confirm changes for substreams!'),
                data: [
                    'stream' => $stream,
                    'sub_streams' => $checkStreamDates['streams'],
                ]
            );
        }

        $timestampStartAt = null;
        if (isset($data['start_at']) && !empty($data['start_at'])) {
            $timestampStartAt = $data['start_at'];
            $data['start_at'] = Carbon::createFromTimestamp($data['start_at'])->format("Y-m-d H:i:s");
        }

        $this->streamRepository->updateByModel($data, $stream);

        $event = $this->streamRepository->getEventByStreamId($id);

        $nearestStream = $this->streamRepository->getNearestStreamByEventId($event['id']);

        if ($timestampStartAt && $timestampStartAt != $streamStartAt) {
            $mailings = $this->streamRepository->getIsDefaultMailingListByEventId($event['id']);
            foreach ($mailings as $item) {
                if ($item['delay_count']) {
                    $newSendAt = Carbon::parse($nearestStream['start_at'])->subMinutes($item['delay_count']);
                    $oldSendAt = Carbon::parse($item['send_at']);
                    if ($newSendAt->timestamp != $oldSendAt->timestamp) {
                        if ($newSendAt->timestamp > now()->timestamp) {
                            CacheServiceFacade::set(CacheKeys::isJobCanceledByUuidKey($item['job_uuid']), true);
                            $newJobId = $this->mailingService->dispathJob($item['id']);
                            $this->mailingRepository->update(['job_uuid' => $newJobId, 'send_at' => $newSendAt->timestamp], $item['id']);
                        }
                    }
                }
            }
        }

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($stream['parent_event_session_id']),
            CacheKeys::eventSessionIdTag($stream['event_session_id']),
            CacheKeys::streamIdTag($stream['id'])
        ])
            ->flush();

        return [
            'stream' => $stream,
            'sub_streams' => $checkStreamDates['streams'] ?? [],
        ];
    }

    public function checkStreamDates($stream, $data)
    {
        if ($stream->parent_event_session_id) {
            $streamStartAt = Carbon::parse($stream->parent_stream_start_at)->timestamp;

            return $this->checkDates($streamStartAt, $data['start_at']);
        } else {
            $streams = $this->streamRepository->listStreamByEventSessionIdForCurrentAuthedUser($stream->event_session_id);

            $subStreams = [];
            foreach ($streams as $streamItem) {
                $subStreamStartAt = Carbon::parse($streamItem->start_at)->timestamp;
                if ($subStreamStartAt < $data['start_at'] || $subStreamStartAt > $data['start_at'] + 12 * 3600) {
                    $subStreams[] = $streamItem;
                }
            }

            if (!empty($subStreams)) {
                return [
                    'status' => false,
                    'streams' => $subStreams,
                ];
            }

            return [
                'status' => true
            ];
        }
    }

    public function checkDates($firstDate, $secondDate)
    {
        if ($secondDate < $firstDate || $secondDate > $firstDate + 12 * 3600) {
            return [
                'status' => false,
                'message' => __('Date must be between ') . $firstDate . __(' and ') . $firstDate + 12 * 3600 . ' '
            ];
        }

        return [
            'status' => true
        ];
    }

    public function updateCoverImg($eventRequestData, $id)
    {
        $this->deleteCoverImg($id);
        $coverImageFilePath = $this->imageService->storeFromFile(
            $eventRequestData['cover'],
            Auth::id(),
            [
                'name_prefix' => 'stream_cover_',
                "w" => 1920,
                "h" => 1080
            ]
        );
        $thumbnailName = 'tmb_' . pathinfo($coverImageFilePath, PATHINFO_FILENAME);
        $coverThumbnailImageFilePath = $this->imageService->storeFromFile(
            $eventRequestData['cover'],
            Auth::id(),
            [
                'w' => 640,
                'h' => 360,
                'name' => $thumbnailName,
            ]
        );

        $stream = $this->streamRepository->update([
            'cover_img_path' => $coverImageFilePath,
        ], $id);

        $eventSessionId = $this->streamRepository->findEventSessionIdById($id);

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($eventSessionId),
            CacheKeys::streamIdTag($id)
        ])
            ->flush();

        return [
            'cover_img_path' => $coverImageFilePath,
            'cover_tmb_img_path' => $coverThumbnailImageFilePath,
        ];
    }

    public function deleteCoverImg($id)
    {
        $filePath = $this->streamRepository->findByIdForCurrentAuthedUser($id)->cover_img_path;
        if (!$filePath) {
            return true;
        }
        $file = str_replace('/storage/', '', $filePath);
        $fileTmb = dirname($file) . '/tmb_' . basename($filePath);

        File::delete(storage_path('app/public/' . $file));
        File::delete(storage_path('app/public/' . $fileTmb));

        $this->streamRepository->update([
            'cover_img_path' => null,
        ], $id);

        $eventSessionId = $this->streamRepository->findEventSessionIdById($id);

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($eventSessionId),
            CacheKeys::streamIdTag($id)
        ])
            ->flush();

        return [
            'cover_img_path' => ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
            'cover_tmb_img_path' => ImagePlaceholders::VIDEO_PLAYER_PLACEHOLDER,
        ];
    }
}
