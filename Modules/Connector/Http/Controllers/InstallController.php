<?php

namespace Modules\Connector\Http\Controllers;

use App\System;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Composer\Semver\Comparator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'connector';
        $this->appVersion = config('connector.module_version');
    }

    /**
     * Install
     *
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        //clear cache & config file
        config(['app.debug' => true]);
        Artisan::call('config:clear');

        //Check if Connector is installed or not.
        $is_installed = System::getProperty($this->module_name . '_version');
        if (!empty($is_installed)) {
            abort(404);
        }

        $action_url = action([\Modules\Connector\Http\Controllers\InstallController::class, 'install']);

        $intruction_type = 'uf';

        return view('connector::install.install-module')
            ->with(compact('action_url', 'intruction_type'));
    }

    /**
     * Installing Connector Module
     */
    public function install()
    {






        try {

            request()->validate([
                'email' => 'required',
                'phone' => 'required',
                'domain' => 'required',
                'license_code' => 'required',
            ]);





            DB::beginTransaction();



            // $client = new Client();
            // $response = Http::post('https://ivoic.egirlsbeauty.com/api/license/check', [
            //     'license' => request()->license_code,
            //     'email' => request()->email,
            //     'phone' => request()->phone,
            //     'domain' => request()->domain,
            //     'module_name' => $this->module_name
            // ]);
            // $res = json_decode($response->body());
            // if ($res->success == false) {
            //     $output = [
            //         'success' => false,
            //         'msg' => $res->msg->msg
            //     ];
            //     return redirect()->back()->with('status', $output);
            // }

            $is_installed = System::getProperty($this->module_name . '_version');
            if (!empty($is_installed)) {
                abort(404);
            }


            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => 'Connector', '--force' => true]);
            Artisan::call('module:publish', ['module' => 'Connector']);
            System::addProperty($this->module_name . '_version', $this->appVersion);

            Artisan::call('passport:install --force');
            //Artisan::call('scribe:generate');

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Connector module installed succesfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()
            ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name . '_version');

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * update module
     *
     * @return Response
     */
    public function update()
    {
        //Check if connector_version is same as appVersion then 404
        //If appVersion > connector_version - run update script.
        //Else there is some problem.
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $connector_version = System::getProperty($this->module_name . '_version');

            if (Comparator::greaterThan($this->appVersion, $connector_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');

                //clear cache & config file
                config(['app.debug' => true]);
                Artisan::call('config:clear');
                //Artisan::call('cache:clear');

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => 'Connector', '--force' => true]);
                Artisan::call('module:publish', ['module' => 'Connector']);
                System::setProperty($this->module_name . '_version', $this->appVersion);

                //Artisan::call('scribe:generate');
            } else {
                abort(404);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Connector module updated Succesfully to version ' . $this->appVersion . ' !!',
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }
}
