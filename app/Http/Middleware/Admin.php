<?php

namespace App\Http\Middleware;

use App\Models\RoleUser;
use App\Repositories\User\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Admin
{
    public function __construct(
        public UserRepository $userRepository
    ) {
        //
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!$this->userRepository->isSuperadmin(Auth::id())){
            throw new AccessDeniedHttpException('Access denied!');
        }

        return $next($request);
    }
}
