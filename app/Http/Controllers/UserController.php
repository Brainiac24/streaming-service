<?php

namespace App\Http\Controllers;

use App\Services\User\UserService;
use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdateGuestDataRequest;
use App\Http\Requests\User\UpdateUserDataRequest;
use App\Http\Resources\User\UserDataResource;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Auth;
use Response;

class UserController extends Controller
{

    static $actionPermissionMap = [
        'getUserData' => 'UserController:getUserData',
        'updateUserData' => 'UserController:updateUserData',
        'confirmEmail' => 'UserController:confirmEmail',
        'updateAvatar' => 'UserController:updateAvatar',
        'deleteAvatar' => 'UserController:deleteAvatar'
    ];

    public function __construct(private UserService $userService, private UserRepository $userRepository)
    {
        //
    }

    public function getUserData()
    {
        return Response::apiSuccess(new UserDataResource(Auth::user()));
    }

    public function updateUserData(UpdateUserDataRequest $request)
    {
        $updatedUser = $this->userRepository->update($request->validated(), Auth::id());
        return Response::apiSuccess(new UserDataResource($updatedUser));
    }

    public function updateGuestData(UpdateGuestDataRequest $request)
    {
        $updatedUser = $this->userRepository->update($request->validated(), Auth::id());
        return Response::apiSuccess(new UserDataResource($updatedUser));
    }

    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $updatedUser = $this->userService->updateAvatar($request->validated());
        return Response::apiSuccess(new UserDataResource($updatedUser));
    }

    public function deleteAvatar()
    {
        $this->userService->deleteAvatar();
        return Response::apiSuccess();
    }
}
