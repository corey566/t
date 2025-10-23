<?php

namespace Modules\Hcm\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'hcm';
        $this->appVersion = config('hcm.module_version');
        $this->module_display_name = 'HCM (Havelock City Mall)';
    }

    /**
     * Install
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if installed or not.
        $is_installed = System::getProperty($this->module_name.'_version');
        if (! empty($is_installed)) {
            abort(404);
        }

        $action_url = action([\Modules\Hcm\Http\Controllers\InstallController::class, 'install']);
        $intruction_type = 'hcm';
        $action_type = 'install';
        $module_display_name = $this->module_display_name;

        return view('install.install-module')
            ->with(compact('action_url', 'intruction_type', 'action_type', 'module_display_name'));
    }

    /**
     * Installing HCM Module
     */
    public function install()
    {
        try {
            request()->validate(
                ['license_code' => 'required',
                    'login_username' => 'required', ],
                ['license_code.required' => 'License code is required',
                    'login_username.required' => 'Username is required', ]
            );

            $license_code = request()->license_code;
            $email = request()->email;
            $login_username = request()->login_username;
            $pid = config('hcm.pid');

            // Check if already installed
            $is_installed = System::getProperty($this->module_name.'_version');
            if (! empty($is_installed)) {
                $output = [
                    'success' => false,
                    'msg' => 'HCM module is already installed',
                ];
                return redirect()->back()->with('status', $output);
            }

            DB::beginTransaction();

            //Validate license (if needed)
            // $response = pos_boot(url('/'), __DIR__, $license_code, $email, $login_username, $type = 1, $pid);
            // if (! empty($response)) {
            //     return $response;
            // }

            // Clear any cached configurations
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            DB::statement('SET default_storage_engine=INNODB;');

            // Run migrations
            $migrateResult = Artisan::call('module:migrate', ['module' => 'Hcm', '--force' => true]);

            if ($migrateResult !== 0) {
                throw new \Exception('Migration failed. Please check your database configuration.');
            }

            // Set module version
            System::addProperty($this->module_name.'_version', $this->appVersion);

            // Add HCM specific permissions and settings
            $this->addHcmPermissions();
            $this->initializeHcmSettings();

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'HCM module installed successfully! You can now configure your tenant settings.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('HCM Installation Error - File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'Installation failed: ' . $e->getMessage(),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Initialize all install functions
     */
    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
    }

    //Updating
    public function update()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $hcm_version = System::getProperty($this->module_name.'_version');

            if (Comparator::greaterThan($this->appVersion, $hcm_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => 'Hcm', '--force' => true]);

                System::setProperty($this->module_name.'_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'HCM module updated successfully to version '.$this->appVersion.' !!',
            ];

            return redirect()
                ->action([\App\Http\Controllers\HomeController::class, 'index'])
                ->with('status', $output);
        } catch (Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name.'_version');

            // Remove HCM specific permissions and settings
            $this->removeHcmPermissions();
            $this->removeHcmSettings();

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Checks if the module is installed or not.
     *
     * @return Response
     */
    public function installed()
    {
        $is_installed = System::getProperty($this->module_name.'_version');

        if (empty($is_installed)) {
            return false;
        }

        return true;
    }

    /**
     * Add HCM specific permissions to Ultimate POS
     */
    private function addHcmPermissions()
    {
        // Add HCM permissions to the system
        $permissions = [
            'hcm.access' => 'Access HCM Module',
            'hcm.configure' => 'Configure HCM Settings',
            'hcm.sync' => 'Sync Data with HCM',
            'hcm.reports' => 'View HCM Reports',
            'hcm.logs' => 'View HCM Logs'
        ];

        foreach ($permissions as $permission => $description) {
            System::addProperty('permission_' . $permission, $description);
        }
    }

    /**
     * Initialize default HCM settings
     */
    private function initializeHcmSettings()
    {
        // Set default HCM configuration
        $defaultSettings = [
            'hcm_api_timeout' => '30',
            'hcm_sync_enabled' => '1',
            'hcm_auto_sync' => '1',
            'hcm_ping_interval' => '300', // 5 minutes
            'hcm_retry_attempts' => '3',
            'hcm_log_retention_days' => '30'
        ];

        foreach ($defaultSettings as $key => $value) {
            System::addProperty($key, $value);
        }
    }

    /**
     * Remove HCM specific permissions
     */
    private function removeHcmPermissions()
    {
        $permissions = [
            'hcm.access',
            'hcm.configure',
            'hcm.sync',
            'hcm.reports',
            'hcm.logs'
        ];

        foreach ($permissions as $permission) {
            System::removeProperty('permission_' . $permission);
        }
    }

    /**
     * Remove default HCM settings
     */
    private function removeHcmSettings()
    {
        $settings = [
            'hcm_api_timeout',
            'hcm_sync_enabled',
            'hcm_auto_sync',
            'hcm_ping_interval',
            'hcm_retry_attempts',
            'hcm_log_retention_days'
        ];

        foreach ($settings as $key) {
            System::removeProperty($key);
        }
    }
}