<?php

namespace App\Http\Middleware;

use App\Exceptions\AccessForbiddenException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckXAuthTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Auth-Token') && $request->header('X-Auth-Token') == config('auth.x_auth_token'))
        {
            return $next($request);
        }

        throw new AccessForbiddenException();
    }
}
