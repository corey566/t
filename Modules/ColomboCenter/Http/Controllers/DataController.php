<?php

namespace Modules\ColomboCenter\Http\Controllers;

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
                'name' => 'colombocenter_module',
                'label' => __('ColomboCenter'),
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

        if ($module_util->hasThePermissionInSubscription($business_id, 'colombocenter_module')) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\ColomboCenter\Http\Controllers\ColomboCenterController::class, 'index']),
                    __('ColomboCenter Integration'),
                    ['icon' => 'fa fas fa-building', 'active' => request()->segment(1) == 'colombocenter', 'style' => config('app.env') == 'demo' ? 'background-color: #f39c12 !important;' : '']
                )->order(89);
            });
        }
    }
}
