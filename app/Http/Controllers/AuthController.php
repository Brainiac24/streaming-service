<?php

namespace App\Http\Controllers;

use App\Constants\StatusCodes;
use App\Constants\WebSocketMutations;
use App\Constants\WebSocketScopes;
use App\Exceptions\UnknownException;
use App\Exceptions\WrongCredentialException;
use App\Http\Requests\Auth\AuthGuestRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ConfirmEmailRequest;
use App\Http\Requests\Auth\EmailRequest;
use App\Http\Requests\Auth\LoginByOTPRequest;
use App\Http\Requests\Auth\LoginByPasswordRequest;
use App\Http\Requests\Auth\RegisterGuestRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RegisterGuestResource;
use App\Http\Resources\BaseJsonResource;
use App\Repositories\EventSession\EventSessionRepository;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\Services\WebSocket\WebSocketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        private WebSocketService $webSocketService,
        private EventSessionRepository $eventSessionRepository
    ) {
    }

    public function checkMustLoginOrRegister(EmailRequest $request)
    {
        return Response::apiSuccess(
            new BaseJsonResource(meta: ["next_action" => $this->authService->checkMustLoginOrRegister(strtolower(trim($request->email)))])
        );
    }

    public function sendOTP(EmailRequest $request)
    {
        $this->authService->sendOTP(strtolower(trim($request->email)), __('Login code for Example'));

        return Response::apiSuccess();
    }

    public function loginByPassword(LoginByPasswordRequest $request)
    {
        if (!$this->authService->login(strtolower(trim($request->email)), $request->password)) {
            throw new WrongCredentialException();
        }

        return Response::apiSuccess(new LoginResource(
            accessToken: $this->authService->createAccessToken(),
            refreshToken: $this->authService->createRefreshToken()
        ));
    }

    public function loginByOTP(LoginByOTPRequest $request)
    {
        $this->authService->loginByOTPToken(strtolower(trim($request->email)), $request->code);

        return Response::apiSuccess(new LoginResource(
            accessToken: $this->authService->createAccessToken(),
            refreshToken: $this->authService->createRefreshToken()
        ));
    }

    public function register(RegisterRequest $request)
    {
        $this->authService->register($request);

        return Response::apiSuccess(new LoginResource(
            accessToken: $this->authService->createAccessToken(),
            refreshToken: $this->authService->createRefreshToken()
        ));
    }

    public function registerGuest(RegisterGuestRequest $request)
    {
        $requestData = $request->validated();
        $session = $this->eventSessionRepository->findByKey($requestData['session']);

        $user = $this->authService->registerGuest($requestData);

        return Response::apiSuccess(new RegisterGuestResource(
            $requestData['type'],
            $session['key'],
            $user['token']
        ));
    }

    public function loginGuest(AuthGuestRequest $request)
    {
        $this->authService->loginGuest($request->validated());

        return Response::apiSuccess(new LoginResource(
            accessToken: $this->authService->createAccessToken(),
            refreshToken: $this->authService->createShortTimeRefreshToken()
        ));
    }

    public function refreshToken()
    {
        Auth::refreshToken();

        return Response::apiSuccess(new LoginResource(
            accessToken: $this->authService->createAccessToken()
        ));
    }

    public function logout()
    {
        if (!$this->authService->logout()) {
            throw new UnknownException();
        }

        return Response::apiSuccess();
    }

    public function confirmEmail(ConfirmEmailRequest $request)
    {
        try {
            $user = $this->authService->confirmEmailByAuthKey($request->authkey);

            $socketData = new BaseJsonResource(
                data: [
                    'is_verified' => true
                ],
                mutation: WebSocketMutations::SOCK_USER_VERIFIED,
                scope: WebSocketScopes::CMS
            );
            $this->webSocketService->publish($user->channel, $socketData);
        } catch (\Throwable $th) {
            return redirect()->away(config('app.frontend_url') . '/my/account/profile/verify_error');
        }

        return redirect()->away(config('app.frontend_url') . '/my/account/profile/verify_success');
    }

    public function requestConfirmEmail(EmailRequest $request)
    {
        $user = $this->userService->getUserByEmail(strtolower(trim($request->email)));

        if (!$user) {
            throw new WrongCredentialException();
        }

        $this->authService->sendConfirmationEmail($user);

        return Response::apiSuccess();
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->userService->changePassword($request->code, $request->password);

        return Response::apiSuccess();
    }
}
