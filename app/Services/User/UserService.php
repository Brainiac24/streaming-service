<?php

namespace App\Services\User;

use App\Constants\CacheKeys;
use App\Exceptions\Auth\TokenErrorException;
use App\Exceptions\WrongCredentialException;
use App\Services\Image\ImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Repositories\User\UserRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Otp\OtpService;

class UserService
{

    public function __construct(public UserRepository $userRepository, public ImageService $imageService, public OtpService $otpService)
    {
    }

    public function getUserByEmail($email)
    {
        $user = CacheServiceFacade::remember(CacheKeys::userEmailKey($email), config('cache.ttl'), function () use ($email) {
            return $this->userRepository->findByEmail($email);
        });

        if ($user) {
            return $user;
        }

        return false;
    }

    public function create($data)
    {
        $data['email'] = strtolower(trim($data['email']));
        $user = $this->getUserByEmail($data['email']);

        if ($user) {
            $user = $this->userRepository->update($data, $user->id);
        } else {
            $user = $this->userRepository->create($data);
        }

        return $user;
    }

    public function changePassword($code, $password)
    {
        $this->otpService->checkOTPTryCountLimit(Auth::user());

        $otpKey = CacheKeys::OTPKey(Auth::user()->email, $code);

        $userId = CacheServiceFacade::get($otpKey);

        if (empty($userId)) {
            throw new TokenErrorException();
        }

        if ($userId != Auth::id()) {
            throw new WrongCredentialException();
        }

        return $this->userRepository->update([
            'password' => Hash::make($password),
        ], Auth::id());
    }


    public function updateAvatar($data)
    {
        $avatarFilePath = $this->imageService->storeFromBase64($data['avatar'], Auth::id(), ['name_prefix' => 'avatar_']);

        return $this->userRepository->update([
            'avatar_path' => $avatarFilePath,
        ], Auth::id());
    }
    public function deleteAvatar()
    {
        $filePath = Auth::user()->avatar_path;

        if (Storage::delete(storage_path("app/" . $filePath))) {
            return $this->userRepository->update([
                'avatar_path' => null,
            ], Auth::id());
        }

        return true;
    }


    public function updateBalance($userId, $amount)
    {

        if ($userId != Auth::id()) {
            throw new WrongCredentialException();
        }

        return $this->userRepository->update([
            'balance' => floatval(Auth::user()->balance) - floatval($amount),
        ], Auth::id());
    }


    public function addToBalance($userId, $amount)
    {
        $user = $this->userRepository->findById($userId);

        $user = $this->userRepository->update([
            'balance' => floatval($user->balance) + floatval($amount),
        ], $user->id);

        return $user;
    }

    public function substractFromBalance($userId, $amount)
    {
        if ($userId != Auth::id()) {
            throw new WrongCredentialException();
        }

        return $this->userRepository->update([
            'balance' => floatval(Auth::user()->balance) - floatval($amount),
        ], Auth::id());
    }

    public function list(){
        return $this->userRepository->getVerifiedList();
    }
}
