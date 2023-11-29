<?php

namespace App\Repositories\PollOptionVote;

use App\Models\PollOptionVote;
use App\Repositories\BaseRepository;
use Auth;

class PollOptionVoteRepository extends BaseRepository
{
    public function __construct(public PollOptionVote $pollOptionVote)
    {
        parent::__construct($pollOptionVote);
    }

    public function findByPollIdAndPollOptionId($pollId, $pollOptionId)
    {
        return $this->pollOptionVote
            ->join('poll_options', function ($join) use ($pollId, $pollOptionId) {
                $join
                    ->on('poll_options.id', '=', 'poll_option_votes.poll_option_id')
                    ->where('poll_options.poll_id', '=', $pollId)
                    ->where('poll_options.id', '=', $pollOptionId);
            })
            ->where('user_id', '=', Auth::id())
            ->first();
    }

    public function findByPollIdAndPluckId($pollId)
    {
        return $this->pollOptionVote
            ->join('poll_options', function ($join) use ($pollId) {
                $join
                    ->on('poll_options.id', '=', 'poll_option_votes.poll_option_id')
                    ->where('poll_options.poll_id', '=', $pollId);
            })
            ->where('user_id', '=', Auth::id())
            ->pluck('poll_option_votes.id');
    }
}
