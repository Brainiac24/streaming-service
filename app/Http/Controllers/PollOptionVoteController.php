<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollOptionVote\CreatePollOptionVoteRequest;
use App\Http\Requests\PollOptionVote\UpdatePollOptionVoteRequest;

class PollOptionVoteController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PollOptionVoteController:list',
        'findById' => 'PollOptionVoteController:findById',
        'create' => 'PollOptionVoteController:create',
        'update' => 'PollOptionVoteController:update',
        'delete' => 'PollOptionVoteController:delete'
    ];

    public function __construct()
    {
        //
    }

    public function list()
    {
        //
    }


    public function findById($id)
    {
        //
    }


    public function create(CreatePollOptionVoteRequest $request)
    {
        //
    }


    public function update(UpdatePollOptionVoteRequest $request, $id)
    {
        //
    }


    public function delete($id)
    {
        //
    }
}
