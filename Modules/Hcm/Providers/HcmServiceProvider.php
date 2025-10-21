<?php

namespace Modules\Hcm\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class HcmServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Hcm';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'hcm';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        
        // Register HCM specific view composers for Ultimate POS
        $this->registerViewComposers();
        
        // Register HCM blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        
        // Register HCM services
        $this->registerHcmServices();
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
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Register HCM specific services
     */
    protected function registerHcmServices()
    {
        // Register HCM utilities and services for Ultimate POS
        $this->app->singleton('hcm.util', function ($app) {
            return new \Modules\Hcm\Utils\HcmUtil();
        });
    }

    /**
     * Register view composers for Ultimate POS integration
     */
    protected function registerViewComposers()
    {
        // Share HCM status with all views in Ultimate POS
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $hcmInstalled = \App\System::getProperty('hcm_version');
                $view->with('hcm_installed', !empty($hcmInstalled));
            }
        });
    }

    /**
     * Register HCM blade directives
     */
    protected function registerBladeDirectives()
    {
        // @hcm_enabled directive for Ultimate POS templates
        Blade::directive('hcm_enabled', function () {
            return "<?php if(\\App\\System::getProperty('hcm_version')): ?>";
        });

        Blade::directive('endhcm_enabled', function () {
            return "<?php endif; ?>";
        });

        // @hcm_sync_status directive
        Blade::directive('hcm_sync_status', function () {
            return "<?php echo \\App\\System::getProperty('hcm_sync_enabled') ? 'Enabled' : 'Disabled'; ?>";
        });
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
