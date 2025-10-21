<?php

namespace Modules\Gallface\Http\Controllers;

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
                'name' => 'gallface_module',
                'label' => __('Gallface'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds cms menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
		$business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_gallface_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'gallface_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        //if ($is_gallface_enabled) {  
		if (auth()->user()->can('gallface.access_gallface_module') && $is_gallface_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(action([\Modules\Gallface\Http\Controllers\GallfaceController::class, 'dashboard']), __('Gallface'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M3 21l18 0"></path>
                    <path d="M3 10l18 0"></path>
                    <path d="M5 6l7 -3l7 3"></path>
                    <path d="M4 10l0 11"></path>
                    <path d="M20 10l0 11"></path>
                    <path d="M8 14l0 3"></path>
                    <path d="M12 14l0 3"></path>
                    <path d="M16 14l0 3"></path>
                  </svg>', 'style' => config('app.env') == 'demo' ? 'background-color: #D483D9;color:white' : '', 'active' => request()->segment(1) == 'gallface'])->order(51);
                }
            );
        }
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'gallface.access_gallface_module',
                'label' => __('accounting::lang.access_accounting_module'),
                'default' => false,
            ],
            [
                'value' => 'gallface.manage_accounts',
                'label' => __('gallface::lang.manage_accounts'),
                'default' => false,
            ],
            [
                'value' => 'gallface.view_journal',
                'label' => __('gallface::lang.view_journal'),
                'default' => false,
            ],
            [
                'value' => 'gallface.add_journal',
                'label' => __('gallface::lang.add_journal'),
                'default' => false,
            ],
            [
                'value' => 'gallface.edit_journal',
                'label' => __('gallface::lang.edit_journal'),
                'default' => false,
            ],
            [
                'value' => 'gallface.delete_journal',
                'label' => __('gallface::lang.delete_journal'),
                'default' => false,
            ],
            [
                'value' => 'gallface.map_transactions',
                'label' => __('gallface::lang.map_transactions'),
                'default' => false,
            ],
            [
                'value' => 'gallface.view_transfer',
                'label' => __('gallface::lang.view_transfer'),
                'default' => false,
            ],
            [
                'value' => 'gallface.add_transfer',
                'label' => __('gallface::lang.add_transfer'),
                'default' => false,
            ],
            [
                'value' => 'gallface.edit_transfer',
                'label' => __('gallface::lang.edit_transfer'),
                'default' => false,
            ],
            [
                'value' => 'gallface.delete_transfer',
                'label' => __('gallface::lang.delete_transfer'),
                'default' => false,
            ],
            [
                'value' => 'gallface.manage_budget',
                'label' => __('gallface::lang.manage_budget'),
                'default' => false,
            ],
            [
                'value' => 'gallface.view_reports',
                'label' => __('gallface::lang.view_reports'),
                'default' => false,
            ],
        ];
    }
}
