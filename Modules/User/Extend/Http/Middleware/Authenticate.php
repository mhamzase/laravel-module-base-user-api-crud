<?php

namespace Modules\User\Extend\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Authenticate extends ProxyAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        return $next($request);
    }
}
