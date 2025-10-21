
<?php

namespace Modules\GallfaceMims\Http\Controllers;

use App\Business;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'gallfacemims_module',
                'label' => __('Gallface Mims'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds module menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        if ($module_util->hasThePermissionInSubscription($business_id, 'gallfacemims_module')) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\GallfaceMims\Http\Controllers\GallfaceMimsController::class, 'index']),
                    __('Gallface Mims Integration'),
                    ['icon' => 'fa fas fa-shopping-cart', 'active' => request()->segment(1) == 'gallfacemims', 'style' => config('app.env') == 'demo' ? 'background-color: #f39c12 !important;' : '']
                )->order(90);
            });
        }
    }
}
