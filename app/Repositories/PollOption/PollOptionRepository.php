<?php

namespace App\Repositories\PollOption;

use App\Models\PollOption;
use App\Repositories\BaseRepository;

class PollOptionRepository extends BaseRepository
{
    public function __construct(public PollOption $pollOption)
    {
        parent::__construct($pollOption);
    }

    public function deleteByPollId($pollId)
    {
        return $this->pollOption->where('poll_id', $pollId)->delete();
    }

    public function incrementVoteCount($id)
    {
        $chatMessage = $this->pollOption->findOrFail($id);

        $chatMessage->votes_count = (int)$chatMessage->votes_count + 1;
        $chatMessage->save();

        return $chatMessage;
    }
}
