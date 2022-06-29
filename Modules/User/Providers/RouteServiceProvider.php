<?php

namespace Modules\User\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\User\Extend\Http\Middleware\Authenticate;
use Modules\Base\Proxies\Http\Middleware\PublicRoutes;
use Modules\User\Http\Middleware\AuthPermission;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\User\Proxies\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $this->extendMiddlewares();
    }

    /**
     * Registering custom middleware Aliases
     */
    public function extendMiddlewares()
    {
        $middlewareGroups = $this->getMiddlewareGroups();

        if(config('user.enable_api_auth', true))
        {
            $middlewareGroups['whf.private']['sanctum']    = 'auth:sanctum';
        }

        if(config('user.enable_permissions_auth', true) && config('user.enable_api_auth', true))
        {
            $middlewareGroups['whf.private']['permission'] = AuthPermission::class;
        }

        $this->middlewareGroup('whf.private', $middlewareGroups['whf.private']);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapPrivateRoutes();
        $this->mapPublicRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('User', '/Routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('User', '/Routes/api.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPrivateRoutes()
    {
        Route::prefix('api/v1/')
            ->middleware(['api', 'whf.private'])
            ->namespace($this->moduleNamespace)
            ->group(module_path('User', '/Routes/v1/private.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPublicRoutes()
    {
        Route::prefix('api/v1/public')
            ->middleware(['api', 'whf.public'])
            ->namespace($this->moduleNamespace)
            ->group(module_path('User', '/Routes/v1/public.php'));
    }
}
