<?php


namespace App\Services\Auth;

use App\Constants\AuthActions;
use App\Constants\CacheKeys;
use App\Constants\EventTicketStatuses;
use App\Constants\EventTicketTypes;
use App\Constants\Roles;
use App\Exceptions\Auth\EmailIsAlreadyVerifiedException;
use App\Exceptions\ValidationException;
use App\Exceptions\WrongCredentialException;
use App\Http\Requests\Auth\RegisterRequest;
use App\Notifications\EmailConfirmationNotification;
use App\Repositories\Event\EventRepository;
use App\Repositories\EventTicket\EventTicketRepository;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Contact\ContactService;
use App\Services\EventAccess\EventAccessService;
use App\Services\Jwt\JwtService;
use App\Services\Otp\OtpService;
use App\Services\User\UserService;
use Auth;
use Str;

class AuthService
{

    public function __construct(
        public JwtService $jwtService,
        public UserService $userService,
        public OtpService $otpService,
        public ContactService $contactService,
        public EventRepository $eventRepository,
        public EventAccessService $eventAccessService,
        public EventTicketRepository $eventTicketRepository
    ) {
    }

    public function login($email, $password)
    {
        return Auth::validate(
            [
                'email' => strtolower(trim($email)),
                'password' => $password,
            ]
        );
    }

    public function loginByOTPToken($email, $OTPToken)
    {
        $email = strtolower(trim($email));
        $user = $this->userService->getUserByEmail($email);
        if (!$user) {
            throw new ValidationException(__('Validation error: User is not found by provided email address!'));
        }

        $this->otpService->checkOTPTryCountLimit($user);

        return Auth::authenticateByOTPToken($email, $OTPToken);
    }

    public function createAccessToken()
    {
        return $this->jwtService->createAccessToken(Auth::user());
    }

    public function createRefreshToken()
    {
        return $this->jwtService->createRefreshToken(Auth::user());
    }


    public function createShortTimeRefreshToken()
    {
        return $this->jwtService->createRefreshToken(Auth::user(), now()->addSeconds(config('jwt.short_refresh_ttl')));
    }

    public function logout()
    {
        return Auth::logout();
    }


    public function register(RegisterRequest $request)
    {

        $email = strtolower(trim($request->email));
        $user = $this->userService->getUserByEmail($email);

        if ($user) {
            throw new WrongCredentialException(__('Wrong credentials error: User by provided email is already exist!'));
        }

        $user = $this->userService->create($request->validated());

        $user->attachRole(Roles::REGISTERED);

        $this->sendConfirmationEmail($user);

        CacheServiceFacade::forget(CacheKeys::userEmailKey($email));

        Auth::setUser($user);

        $contacts = $this->contactService->allByEmail($user['email']);
        if (count($contacts) > 0) {
            foreach ($contacts as $contact) {
                $event = $this->eventRepository->findById($contact['event_id']);

                if (!$event['is_unique_ticket_enabled'] && !$event['is_multi_ticket_enabled']) {
                    $user->attachRoleAndEventTicketToAccessGroup(Roles::MEMBER, $contact['event_ticket_id'], $event['access_group_id']);
                } else if (isset($contact['event_ticket_id']) && !empty($contact['event_ticket_id'])) {
                    $eventTicket = $this->eventTicketRepository->findById($contact['event_ticket_id']);
                    if ($eventTicket['event_ticket_status_id'] == EventTicketStatuses::ACTIVE) {
                        $user->attachRoleAndEventTicketToAccessGroup(Roles::MEMBER, $contact['event_ticket_id'], $event['access_group_id']);

                        if ($eventTicket['event_ticket_type_id'] == EventTicketTypes::UNIQUE) {
                            $this->eventTicketRepository->updateByModel(
                                [
                                    'event_ticket_status_id' => EventTicketStatuses::USED,
                                    'user_id'                => $user['id'],
                                    'contact_id'             => $contact['id'],
                                ],
                                $eventTicket
                            );
                        }

                    }
                }
            }
        }

        return $user;
    }

    public function registerGuest($requestData)
    {
        $user = null;
        if ($requestData['token'] ?? false) {
            $user = $this->userService->userRepository->findByToken($requestData['token']);
        }

        if (!$user) {
            $user = $this->userService->userRepository->createGuest([
                'token' => Str::random(32)
            ]);
        }

        Auth::setUser($user);

        return $user;
    }

    public function loginGuest($requestData)
    {
        $user = null;
        if ($requestData['token'] ?? false) {
            $user = $this->userService->userRepository->findByToken($requestData['token']);
        }

        if (!$user) {
            throw new WrongCredentialException(__('Wrong credentials error: User by provided token not found!'));
        }

        Auth::setUser($user);

        return $user;
    }

    public function checkMustLoginOrRegister($email)
    {
        $user = $this->userService->getUserByEmail($email);

        $action = AuthActions::REGISTRATION;

        if ($user) {
            $action = AuthActions::LOGIN;
            $this->otpService->checkOTPSentCountLimit($user);
            $this->otpService->sendOTPToken($user, __('Login code for Example'));
        }

        return $action;
    }


    public function sendConfirmationEmail($user)
    {
        return $user->notify(new EmailConfirmationNotification($this->generateAuthKey($user->id)));
    }

    public function sendOTP($email, $title)
    {
        $user = $this->userService->getUserByEmail($email);
        if (!$user) {
            throw new ValidationException(__('Validation error: User is not found by provided email address!'));
        }

        $this->otpService->checkOTPSentCountLimit($user);
        $this->otpService->sendOTPToken($user, $title);
        $this->otpService->resetOTPTryCountLimit($user);
    }



    public function generateAuthKey($userId)
    {
        $authKey = Str::random(32);
        CacheServiceFacade::set(CacheKeys::authKey($authKey), $userId, now()->addMinutes(config('auth.email_confirmation_timeout_minute'))->timestamp);
        return $authKey;
    }


    public function confirmEmailByAuthKey($authKey)
    {

        $userId = CacheServiceFacade::get(CacheKeys::authKey($authKey));

        if (!$userId) {
            throw new WrongCredentialException();
        }

        $user = $this->userService->userRepository->findById($userId);

        if ($user?->is_verified == true) {
            throw new EmailIsAlreadyVerifiedException();
        }

        $user = $this->userService->userRepository->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ], $userId);

        return $user;
    }
}
