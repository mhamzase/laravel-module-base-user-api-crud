<?php

namespace Modules\User\Http\Middleware;

use Closure;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Entities\PermissionAlias;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class AuthPermission
{

    /**
     * responsible for checking route permission (direct or via roles)
     *
     * as this is using hasAnyPermission trait that is responsible for checking all kind of permisssions (direct, roles or permission alias)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $route =  \Request::route()->getName();

        if($user && !$user->hasAnyPermission($route))
        {
            return new JsonResponse(['error' => 'you do not have permissions to perform this action'], 403);
        }

        return $next($request);
    }

}
