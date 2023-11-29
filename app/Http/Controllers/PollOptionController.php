<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollOption\CreatePollOptionRequest;
use App\Http\Requests\PollOption\UpdatePollOptionRequest;

class PollOptionController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'PollOptionController:list',
        'findById' => 'PollOptionController:findById',
        'create' => 'PollOptionController:create',
        'update' => 'PollOptionController:update',
        'delete' => 'PollOptionController:delete'
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


    public function create(CreatePollOptionRequest $request)
    {
        //
    }


    public function update(UpdatePollOptionRequest $request, $id)
    {
        //
    }


    public function delete($id)
    {
        //
    }
}
