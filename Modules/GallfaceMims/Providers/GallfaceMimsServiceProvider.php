<?php

namespace Modules\GallfaceMims\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Console\Scheduling\Schedule;

class GallfaceMimsServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'GallfaceMims';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'gallfacemims';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('GallfaceMims', 'Database/Migrations'));
        $this->registerCommands();
        $this->registerEventListeners();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands.
     */
    public function registerCommands()
    {
        // Register commands when they are created
        // $this->commands([
        //     \Modules\GallfaceMims\Console\SyncCommand::class,
        // ]);
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        $events = $this->app->make('events');

        // Listen for sale created event
        $events->listen(
            'eloquent.created: App\Transaction',
            function ($transaction) {
                // Handle transaction sync if needed
            }
        );
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
}