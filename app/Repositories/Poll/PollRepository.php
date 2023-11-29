<?php

namespace App\Repositories\Poll;

use App\Constants\CacheKeys;
use App\Models\Poll;
use App\Repositories\BaseRepository;
use App\Services\Cache\CacheServiceFacade;

class PollRepository extends BaseRepository
{
    public function __construct(public Poll $poll)
    {
        parent::__construct($poll);
    }

    public function listByEventSessionIdWithOptions($eventSessionId)
    {

        $cacheKey = CacheKeys::pollsByEventSessionIdWithOptions($eventSessionId);

        $poll = CacheServiceFacade::get($cacheKey);

        if (!$poll) {
            $poll = $this->poll
                ->leftJoin('poll_options', 'poll_options.poll_id', '=', 'polls.id')
                ->leftJoin('poll_option_votes', 'poll_option_votes.poll_option_id', '=', 'poll_options.id')
                ->where('event_session_id', $eventSessionId)
                ->orderBy('polls.id', 'desc')
                ->orderBy('poll_options.id', 'asc')
                ->get([
                    'polls.*',
                    'poll_options.id as poll_option_id',
                    'poll_options.name as poll_option_name',
                    'poll_options.votes_count as poll_option_votes_count',
                    'poll_options.poll_id as poll_option_poll_id',
                    'poll_options.created_at as poll_option_created_at',
                    'poll_options.updated_at as poll_option_updated_at',
                    'poll_option_votes.id as poll_option_vote_id',
                    'poll_option_votes.poll_option_id as poll_option_vote_poll_option_id',
                    'poll_option_votes.user_id as poll_option_vote_user_id',
                ]);

            if ($poll->isNotEmpty()) {

                $pollIds = [];
                foreach ($poll as $pollItem) {
                    if (!in_array($pollItem['id'], $pollIds)) {
                        $pollIds[] = $pollItem['id'];
                    }
                }

                CacheServiceFacade::tags([
                    CacheKeys::eventSessionIdTag($poll[0]['event_session_id']),
                    ...CacheKeys::setPollIdTags($pollIds)
                ])
                    ->set($cacheKey, $poll, config('cache.ttl'));
            }
        }



        return $poll;
    }

    public function accessGroupIdByPollId($pollId)
    {
        return CacheServiceFacade::remember(CacheKeys::accessGroupByPollIdKey($pollId), config('cache.ttl'), function () use ($pollId) {
            return $this->poll
                ->join('event_sessions', 'event_sessions.id', '=', 'polls.event_session_id')
                ->join('events',  'events.id', '=', 'event_sessions.event_id')
                ->where('polls.id', '=', $pollId)
                ->first(['events.access_group_id'])
                ->access_group_id;
        });
    }
}
