<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\User\UserAdminListArrayResource;
use App\Http\Resources\Event\AdminUserEventListResource;
use App\Http\Resources\User\UserDataResource;
use App\Repositories\Event\EventRepository;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\UserRepository;
use App\Services\Transaction\TransactionService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Response;

class AdminUserController extends Controller
{

    public function __construct(
        public UserService $userService,
        public UserRepository $userRepository,
        public ProjectRepository $projectRepository,
        public EventRepository $eventRepository,
        public TransactionService $transactionService
    ) {
        //
    }
    public function getUserList(){
        return Response::apiSuccess(
            new UserAdminListArrayResource($this->userService->list())
        );
    }

    public function getUser($user_id){
        $user = $this->userRepository->findById($user_id);

        return Response::apiSuccess(
            new UserDataResource($user)
        );
    }
    public function getUserProjects($user_id){
        $this->userRepository->findById($user_id);
        $projects = $this->projectRepository->allByUserId($user_id);

        return Response::apiSuccess(
            new BaseJsonResource(data: $projects)
        );
    }
    public function getUserEvents($user_id){
        $this->userRepository->findById($user_id);
        $events = $this->eventRepository->allByUserId($user_id);

        return Response::apiSuccess(
            new AdminUserEventListResource($events)
        );
    }
    public function getUserTransactions($user_id){
        $this->userRepository->findById($user_id);

        return Response::apiSuccess(
            new BaseJsonResource(data: $this->transactionService->list($user_id))
        );
    }




}

