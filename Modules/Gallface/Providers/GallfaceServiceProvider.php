<?php

namespace Modules\Gallface\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Console\Scheduling\Schedule;

class GallfaceServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Gallface';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'gallface';

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
        $this->loadMigrationsFrom(module_path('Gallface', 'Database/Migrations'));

        // Register event listener for auto-sync
        $this->app['events']->listen(
            \App\Events\SellCreatedOrModified::class,
            \Modules\Gallface\Listeners\GallfaceSaleCreatedListener::class
        );
    }

    /**
     * Register commands.
     */
    public function registerCommands()
    {
        $this->commands([
            \Modules\Gallface\Console\GallfaceSyncCommand::class,
            \Modules\Gallface\Console\HcmSyncCommand::class,
            \Modules\Gallface\Console\HcmTestPingCommand::class,
            \Modules\Gallface\Console\HcmActivityPingCommand::class,
        ]);
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
            $this->loadJsonTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
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
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        // Listen for transaction/sale created events
        $events = $this->app->make('events');

        // Listen for sale created event
        $events->listen(
            'App\Events\TransactionCreated',
            'Modules\Gallface\Listeners\SaleCreatedListener@handle'
        );

        // Also listen for eloquent created event on Transaction model
        $events->listen(
            'eloquent.created: App\Transaction',
            'Modules\Gallface\Listeners\SaleCreatedListener@handle'
        );

        // Listen for user login event
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'Modules\Gallface\Listeners\UserLoggedInListener@handle'
        );
    }

    /**
     * Schedule automatic syncing tasks
     */
    protected function scheduleAutoSync(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Run Gallface auto-sync every minute for locations with auto_sync_enabled
            $schedule->command('gallface:sync --auto')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();

            // Run HCM auto-sync every minute for locations with auto_sync_enabled
            $schedule->command('hcm:sync --auto')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();

            // Note: HCM ping every 5 seconds is handled by the "Auto Sync Monitor" workflow
            // which runs the commands continuously with sleep intervals
        });
    }
}