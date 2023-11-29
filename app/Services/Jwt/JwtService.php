<?php

namespace App\Services\Jwt;

use App\Constants\TokenTypes;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\User;
use Str;

class JwtService
{
    public function __construct(private JwtBuilderService $jwtBuilderService)
    {
    }

    public function createAccessToken(User $user, CarbonInterface $ttl = null): string
    {
        return $this->jwtBuilderService
            ->issuedBy(config('app.url'))
            ->audience(config('app.name'))
            ->tokenType(TokenTypes::ACCESS)
            ->issuedAt(now()->getTimestamp())
            ->canOnlyBeUsedAfter(now())
            ->expiresAt($ttl ?? now()->addSeconds(config('jwt.ttl')))
            ->relatedTo($user->id)
            ->sessionId(Str::random(20))
            ->generateToken();
    }

    public function createRefreshToken(User $user, CarbonInterface $ttl = null): string
    {
        return $this->jwtBuilderService
            ->issuedBy(config('app.url'))
            ->audience(config('app.name'))
            ->tokenType(TokenTypes::REFRESH)
            ->issuedAt(now()->getTimestamp())
            ->canOnlyBeUsedAfter(now())
            ->expiresAt($ttl ?? now()->addSeconds(config('jwt.refresh_ttl')))
            ->relatedTo($user->id)
            ->sessionId(Str::random(20))
            ->generateToken();
    }
}
