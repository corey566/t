
<?php

namespace Modules\ColomboCenter\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ColomboCenterServiceProvider extends ServiceProvider
{
    protected $moduleName = 'ColomboCenter';
    protected $moduleNameLower = 'colombocenter';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('ColomboCenter', 'Database/Migrations'));
        $this->registerCommands();
        $this->scheduleAutoSync();
        $this->registerEventListeners();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerCommands()
    {
        // Register commands when they are created
        // Example: $this->commands([
        //     \Modules\ColomboCenter\Console\SyncCommand::class,
        // ]);
    }

    protected function scheduleAutoSync(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Add scheduled tasks here when needed
            // Example: $schedule->command('colombocenter:sync --auto')
            //     ->everyMinute()
            //     ->withoutOverlapping()
            //     ->runInBackground();
        });
    }

    protected function registerEventListeners(): void
    {
        $events = $this->app->make('events');
        
        // Listen for transaction/sale created events
        $events->listen(
            'eloquent.created: App\Transaction',
            function ($transaction) {
                // Handle transaction sync if needed
            }
        );
        
        // Listen for user login event
        $events->listen(
            'Illuminate\Auth\Events\Login',
            function ($event) {
                // Handle user login event if needed
            }
        );
    }

    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
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

    public function provides()
    {
        return [];
    }
}
