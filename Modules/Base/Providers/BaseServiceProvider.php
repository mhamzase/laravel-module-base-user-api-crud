<?php

namespace Modules\Base\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Base\Console\CsvTranslations;
use Modules\Base\Console\MakeControllerCommand;
use Modules\Base\Console\MakeMiddlewareCommand;
use Modules\Base\Console\MakeModelCommand;
use Modules\Base\Console\MakeRepositoryCommand;
use Modules\Base\Console\ModuleGenerateCommand;
use Modules\Base\Console\ModuleMakeCommand;
use Modules\Base\Console\TestAPI;
use Modules\Base\Console\WhfMakeControllerCommand;
use Modules\Base\Console\WhfMakeMiddlewareCommand;
use Modules\Base\Console\WhfMakeModelCommand;
use Modules\Base\Console\WhfMakeRepositoryCommand;
use Modules\Base\Proxies\LoadProxy;
use Nwidart\Modules\Facades\Module;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Base';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'base';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerProxies();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->commands([
            MakeControllerCommand::class,
            MakeMiddlewareCommand::class,
            MakeModelCommand::class,
            MakeRepositoryCommand::class,
            ModuleGenerateCommand::class,
            //===WHF Alias Commands
            WhfMakeControllerCommand::class,
            WhfMakeMiddlewareCommand::class,
            WhfMakeModelCommand::class,
            WhfMakeRepositoryCommand::class,
            CsvTranslations::class,
            TestAPI::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    /**
     * Call the helper class to load Class Proxies
     */
    private function registerProxies() {
        LoadProxy::init($this->moduleName);
    }
}
