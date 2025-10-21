
<?php

namespace Modules\GallfaceMims\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'GallfaceMims';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'])
            ->prefix('gallfacemims')
            ->group(module_path('GallfaceMims', '/Routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/gallfacemims')
            ->group(module_path('GallfaceMims', '/Routes/api.php'));
    }
}
