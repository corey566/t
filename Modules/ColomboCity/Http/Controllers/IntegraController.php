<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class IntegraController extends Controller
{
    /**
     * Show Integra API credentials page
     */
    public function credentials()
    {
        return view('gallface::gallface.integra_credentials');
    }

    /**
     * Save Integra API credentials to .env file
     */
    public function saveCredentials(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
                'location_code' => 'nullable|string',
                'terminal_id' => 'nullable|string'
            ]);

            // Update .env file
            $envFile = base_path('.env');
            $envContent = file_get_contents($envFile);

            // Update or add credentials
            $credentials = [
                'INTEGRA_API_USER' => $validated['username'],
                'INTEGRA_API_PASS' => $validated['password'],
                'INTEGRA_LOCATION_CODE' => $validated['location_code'] ?? '01',
                'INTEGRA_TERMINAL_ID' => $validated['terminal_id'] ?? '01'
            ];

            foreach ($credentials as $key => $value) {
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}={$value}",
                        $envContent
                    );
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            file_put_contents($envFile, $envContent);

            // Clear config cache
            \Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Colombo City Center API credentials saved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get API logs
     */
    public function getApiLogs(Request $request)
    {
        try {
            $logs = DB::table('integra_api_logs')
                ->orderBy('created_at', 'desc')
                ->limit($request->input('limit', 50))
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
