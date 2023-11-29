<?php

namespace App\Services\Jwt;

use App\Constants\StatusCodes;
use App\Constants\TokenTypes;
use App\Exceptions\Auth\RefreshTokenErrorException;
use App\Exceptions\Auth\TokenErrorException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtParserService
{
    /**
     * @var array|object
     */
    protected $claims;

    public function __construct(string $token, $tokenType = null)
    {
        JWT::$leeway = $this->getLeeway();
        try {
            $this->claims = JWT::decode($token, new Key($this->getSecretKey(), $this->getEncryptionAlgorytm()));
        } catch (\Throwable $th) {
            if ($th instanceof ExpiredException) {
                if ($tokenType == TokenTypes::REFRESH) {
                    throw new RefreshTokenErrorException();
                }
                throw $th;
            }
            throw new TokenErrorException($th->getMessage(), StatusCodes::UNKNOWN_ERROR, $th);
        }
    }

    public static function loadFromToken(string $token, $tokenType = null)
    {
        return new self($token, $tokenType);
    }

    public function getIssuedBy()
    {
        return $this->getClaim('iss');
    }

    public function getIssuedAt()
    {
        return intval($this->getClaim('iat'));
    }

    public function getRelatedTo()
    {
        return intval($this->getClaim('sub'));
    }

    public function getAudience()
    {
        return $this->getClaim('aud');
    }

    public function getExpiresAt()
    {
        return intval($this->getClaim('exp'));
    }

    public function getIdentifiedBy()
    {
        return $this->getClaim('jti');
    }

    public function getSessionId()
    {
        return $this->getClaim('sid');
    }

    public function getTokenType()
    {
        return $this->getClaim('ttp');
    }

    public function getCanOnlyBeUsedAfter()
    {
        return intval($this->getClaim('nbf'));
    }

    protected function getClaim(string $name)
    {
        return $this->claims->{$name} ?? null;
    }

    protected function getSecretKey(): string
    {
        return config('jwt.secret_key');
    }

    protected function getEncryptionAlgorytm()
    {
        return config('jwt.encryption_algorytm');
    }

    protected function getLeeway()
    {
        return intval(config('jwt.leeway'));
    }
}
