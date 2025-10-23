
<?php

namespace Modules\Hcm\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Adds HCM menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_hcm_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'hcm_module', 'superadmin_package');

        if ($is_hcm_enabled && (auth()->user()->can('superadmin') || auth()->user()->can('hcm.access'))) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\Hcm\Http\Controllers\HcmController::class, 'index']),
                    'HCM Integration',
                    ['icon' => 'fa fa-building', 'active' => request()->segment(1) == 'hcm']
                );
            });
        }
    }

    /**
     * Add relationship to Transaction model
     */
    public function transaction_extra_data()
    {
        return [
            'hcmInvoiceLog' => [
                'type' => 'hasOne',
                'model' => 'Modules\Hcm\Entities\HcmInvoiceLog',
                'foreign_key' => 'transaction_id'
            ]
        ];
    }

    /**
     * Add relationship to Business model
     */
    public function business_extra_data()
    {
        return [
            'hcmTenantConfigs' => [
                'type' => 'hasMany',
                'model' => 'Modules\Hcm\Entities\HcmTenantConfig',
                'foreign_key' => 'business_id'
            ]
        ];
    }
}
