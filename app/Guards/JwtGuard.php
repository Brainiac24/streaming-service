<?php

namespace App\Guards;

use App\Constants\CacheKeys;
use App\Constants\Languages;
use App\Constants\TokenTypes;
use App\Exceptions\Auth\MissingTokenException;
use App\Exceptions\Auth\RefreshTokenErrorException;
use App\Exceptions\Auth\TokenErrorException;
use App\Exceptions\UnknownException;
use App\Exceptions\WrongCredentialException;
use App\Services\Cache\CacheServiceFacade;
use App\Services\Jwt\JwtParserService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class JwtGuard implements Guard
{
    protected Request $request;
    protected UserProvider $provider;
    protected Authenticatable|null $user;
    protected Authenticatable|null $lastAttemptedUser;

    /**
     * Create a new authentication guard.
     *
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->setUser(null);
    }

    /**
     * Determine if the current user is authenticated.
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $this->setUser($this->authenticateByJWTAccessToken());

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     * @return int|null|string
     */
    public function id()
    {
        return $this->user()->getAuthIdentifier();
    }

    public function isUserHasToken()
    {
        try {
            return (bool)$this->user();
        } catch (\Throwable $th) {
        }

        return false;
    }

    private function authenticateByJWTAccessToken()
    {

        if (!empty($this->user)) {
            return $this->user;
        }


        $token = $this->request->bearerToken();

        if (empty($token)) {
            throw new MissingTokenException();
        }

        if ($this->isTokenInBlacklist($token)) {
            throw new TokenErrorException();
        }

        $decoded = $this->parseJwtToken($token);

        if ($decoded->getTokenType() != TokenTypes::ACCESS) {
            throw new TokenErrorException();
        }


        return $this->provider->retrieveById($decoded->getRelatedTo());
    }

    public function refreshToken()
    {

        $this->setUser($this->authenticateByJWTRefreshToken());

        if (empty($this->user)) {
            throw new UnknownException();
        }
    }

    private function authenticateByJWTRefreshToken()
    {
        if (!empty($this->user)) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if (empty($token)) {
            throw new MissingTokenException();
        }

        if ($this->isTokenInBlacklist($token)) {
            throw new RefreshTokenErrorException();
        }

        $decoded = $this->parseJwtToken($token, TokenTypes::REFRESH);

        if ($decoded->getTokenType() != TokenTypes::REFRESH) {
            throw new RefreshTokenErrorException();
        }

        return $this->provider->retrieveById($decoded->getRelatedTo());
    }

    public function logout()
    {
        if (!$this->addToBlacklistJWTToken()) {
            return false;
        }

        $this->setUser(null);

        return true;
    }

    private function addToBlacklistJWTToken()
    {

        $this->setUser(null);

        $token = $this->request->bearerToken();

        if (empty($token)) {
            throw new MissingTokenException();
        }

        if ($this->isTokenInBlacklist($token)) {
            return true;
        }

        $decoded = $this->parseJwtToken($token);

        CacheServiceFacade::set(CacheKeys::blacklistTokenKey($token), true, $decoded->getExpiresAt());

        return true;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = array())
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);
            return true;
        } else {
            return false;
        }
    }

    public function getUserByCredentials(array $credentials = array())
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            return $user;
        } else {
            return false;
        }
    }

    /**
     * Determine if the guard has a user instance.
     * @return bool
     */
    public function hasUser()
    {
        return !is_null($this->user);
    }

    /**
     * Set the current user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable|null $user)
    {
        $this->user = $user;

        if (in_array($this->user?->lang, Languages::AVAILABLE)) {
            App::setLocale($this->user->lang);
        } else {
            App::setLocale(Languages::RU);
        }

        return $this;
    }

    public function parseJwtToken($token, $tokenType = null)
    {
        return JwtParserService::loadFromToken($token, $tokenType);
    }

    public function isTokenInBlacklist($token)
    {
        $blacklistedToken = CacheServiceFacade::get(CacheKeys::blacklistTokenKey($token));

        return is_null($blacklistedToken) ? false : true;
    }

    public function authenticateByOTPToken($email, $OTPToken)
    {
        $userId = CacheServiceFacade::get(CacheKeys::OTPKey($email, $OTPToken));

        if (empty($userId)) {
            throw new WrongCredentialException();
        }

        $this->setUser($this->provider->retrieveById($userId));

        $sentCountKey = CacheKeys::OTPSentCountKey($this->user->email);
        CacheServiceFacade::forget($sentCountKey);

        $tryCountKey = CacheKeys::OTPTryCountKey($this->user->email);
        CacheServiceFacade::forget($tryCountKey);

        return $this->user;
    }

    public function getDecodedJWTToken()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            throw new MissingTokenException();
        }

        if ($this->isTokenInBlacklist($token)) {
            throw new TokenErrorException();
        }

        return $this->parseJwtToken($token);
    }
}
