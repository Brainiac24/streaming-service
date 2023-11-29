<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $controllerName = class_basename(Route::getCurrentRoute()->getControllerClass());
        $actionName = Route::getCurrentRoute()->getActionMethod();

        $controller = Route::getCurrentRoute()->getController();
        $permissionName = $controllerName . ':' . $actionName;

        $hasPermission = $controller::ACTION_PERMISSIONS[$permissionName] ?? false;

        if (!$hasPermission && !Auth::user()->hasPermissionFromAnyRelations($permissionName)) {
            throw new AccessDeniedHttpException('Access denied!');
        }

        return $next($request);
    }
}
