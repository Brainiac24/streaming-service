<?php

namespace App\Services\Poll;

use App\Constants\CacheKeys;
use App\Constants\PollOptionActions;
use App\Constants\PollStatuses;
use App\Constants\PollTypes;
use App\Constants\Roles;
use App\Exceptions\BusinessLogicException;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\Poll\PollRepository;
use App\Repositories\PollOption\PollOptionRepository;
use App\Repositories\PollOptionVote\PollOptionVoteRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\EventAccess\EventAccessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PollService
{
    public function __construct(
        public PollRepository $pollRepository,
        public EventAccessService $eventAccessService,
        public PollOptionRepository $pollOptionRepository,
        public PollOptionVoteRepository $pollOptionVoteRepository,
        public EventSessionRepository $eventSessionRepository
    ) {
    }

    public function list($eventSessionId)
    {
        $polls = $this->pollRepository->listByEventSessionIdWithOptions($eventSessionId);

        $pollResult = [];

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($eventSessionId);

        $isAdminOrModerator = Auth::user()->hasRolesByAccessGroupId($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $cacheKey = CacheKeys::pollsByEventSessionIdAndIsAdminOrModeratorKey($eventSessionId, $isAdminOrModerator);

        foreach ($polls as $poll) {
            if (
                !$isAdminOrModerator &&
                ($poll['poll_status_id'] == PollStatuses::NEW ||
                    ($poll['poll_status_id'] == PollStatuses::FINISHED &&
                        !$poll['is_public_results']))
            ) {
                continue;
            }
            if (!isset($pollResult[$poll['id']])) {
                $pollResult[$poll['id']] = [
                    'id' => $poll['id'],
                    'event_session_id' => $poll['event_session_id'],
                    'question' => $poll['question'],
                    'channel' => $poll['channel'],
                    'private_channel' => $poll['private_channel'],
                    'is_multiselect' => $poll['is_multiselect'],
                    'is_public_results' => $poll['is_public_results'],
                    'poll_type_id' => $poll['poll_type_id'],
                    'poll_status_id' => $poll['poll_status_id'],
                    'start_at' => $poll['start_at'],
                    'created_at' => $poll['created_at'],
                    'updated_at' => $poll['updated_at'],
                ];
            }

            $pollOptions = &$pollResult[$poll['id']]['options'];

            if (empty($poll['poll_option_id'])) {
                $pollOptions = [];
            } elseif (!isset($pollOptions[$poll['poll_option_id']])) {
                $pollOptions[$poll['poll_option_id']] = [
                    'id' => $poll['poll_option_id'],
                    'name' => $poll['poll_option_name'],
                    'votes_count' => $poll['poll_option_votes_count'],
                    'voted_users' => [],
                    'created_at' => $poll['poll_option_created_at'],
                    'updated_at' => $poll['poll_option_updated_at'],
                ];
            }

            if (isset($pollOptions[$poll['poll_option_id']]) && $poll['poll_option_vote_user_id']) {
                $votedUsers = &$pollOptions[$poll['poll_option_id']]['voted_users'];
                $votedUsers[$poll['poll_option_vote_user_id']] = $poll['poll_option_vote_user_id'];
            }
        }
        $pollResult = array_values($pollResult);
        $pollIds = [];
        foreach ($pollResult as &$pollResultItem) {
            $pollIds[] = $pollResultItem['id'];
            $pollResultItem['options'] = array_values($pollResultItem['options']);
        }

        CacheServiceFacade::tags([
            CacheKeys::eventSessionIdTag($eventSessionId),
            ...CacheKeys::setPollIdTags($pollIds)
        ])
            ->set($cacheKey, $pollResult, config('cache.ttl'));

        return $pollResult;
    }

    public function create($data)
    {
        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($data['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        $data['channel'] = Str::lower(Str::random(28));
        $data['private_channel'] = Str::lower(Str::random(28));
        $data['poll_status_id'] = PollStatuses::NEW;
        $data['poll_type_id'] ??= PollTypes::ROWS;

        $poll = $this->pollRepository->create($data);

        foreach ($data['options'] as $option) {
            $this->pollOptionRepository->create([
                'poll_id' => $poll->id,
                'name' => $option['name']
            ]);
        }

        return $poll->with('options')->find($poll->id);
    }

    public function update($data, $id)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        if ($poll['poll_status_id'] != PollStatuses::NEW) {
            throw new BusinessLogicException("Business logic error: Poll could not be updated when it's activated or finished!");
        }

        $poll = $this->pollRepository->updateByModel($data, $poll);
        if (isset($data['options']) && $data['options']) {

            foreach ($data['options'] as $option) {

                if (isset($option['id']) && !empty($option['id'])) {
                    if (isset($option['action']) && $option['action'] == PollOptionActions::DELETE) {
                        $this->pollOptionRepository->delete($option['id']);
                    } else {
                        $this->pollOptionRepository->update([
                            'name' => $option['name']
                        ], $option['id']);
                    }
                } else {
                    $this->pollOptionRepository->create([
                        'poll_id' => $poll->id,
                        'name' => $option['name']
                    ]);
                }
            }
        }

        return $poll->with('options')->find($poll->id);
    }

    public function delete($id)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        $this->pollOptionRepository->deleteByPollId($poll->id);

        return $this->pollRepository->deleteByModel($poll);
    }

    public function updateType($requestData, $id)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        $data = [
            'poll_type_id' => $requestData['poll_type_id']
        ];

        $this->pollRepository->updateByModel($data, $poll);

        return $poll->with('options')->find($poll->id);
    }


    public function updateIsPublicResults($id, $status = true)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        $data = [
            'is_public_results' => $status
        ];

        $this->pollRepository->updateByModel($data, $poll);

        return $poll->with('options')->find($poll->id);
    }


    public function updateStatusToStarted($id)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        $data = [
            'poll_status_id' => PollStatuses::STARTED,
            'start_at' => now()->format('Y-m-d H:i:s')
        ];

        $this->pollRepository->updateByModel($data, $poll);

        return $poll->with('options')->find($poll->id);
    }

    public function updateStatusToFinished($id)
    {
        $poll = $this->checkRolesAndGetPoll($id);

        $data = [
            'poll_status_id' => PollStatuses::FINISHED
        ];

        $this->pollRepository->updateByModel($data, $poll);

        return $poll->with('options')->find($poll->id);
    }


    public function checkRolesAndGetPoll($id)
    {
        $poll = $this->pollRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR]);

        return $poll;
    }

    public function vote($data, $id)
    {
        $poll = $this->pollRepository->findById($id);

        $accessGroupId = $this->eventSessionRepository->accessGroupIdByEventSessionId($poll['event_session_id']);

        Auth::user()->hasRolesByAccessGroupIdOrFail($accessGroupId, [Roles::ADMIN, Roles::MODERATOR, Roles::MEMBER]);

        if ($poll['poll_status_id'] != PollStatuses::STARTED) {
            throw new BusinessLogicException("Business logic error: Poll could not be voted when it's not started!");
        }


        if (!is_array($data['options'])) {
            $data['options'] = [$data['options']];
        }

        foreach ($data['options'] as $optionId) {

            $pollOptionVote = $this->pollOptionVoteRepository->findByPollIdAndPollOptionId($id, $optionId);

            if (!$pollOptionVote) {
                $pollOptionVote = $this->pollOptionVoteRepository->create([
                    'poll_option_id' => $optionId,
                    'user_id' => Auth::id(),
                ]);

                $this->pollOptionRepository->incrementVoteCount($optionId);
            }

            if (!$poll['is_multiselect']) {
                break;
            }
        }

        return $poll->with('options')->find($poll->id);
    }
}
