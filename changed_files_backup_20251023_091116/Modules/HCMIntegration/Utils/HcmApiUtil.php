
<?php

namespace Modules\HCMIntegration\Utils;

use Modules\HCMIntegration\Entities\HcmTenantConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HcmApiUtil
{
    protected $config;
    protected $timeout;

    public function __construct(HcmTenantConfig $config)
    {
        $this->config = $config;
        $this->timeout = config('hcmintegration.api_timeout', 30);
    }

    /**
     * Authenticate and get access token
     */
    public function authenticate()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->config->api_url . '/api/token', [
                    'grant_type' => 'test_credentials',
                    'tenant_id' => $this->config->username,
                    'tenant_secret' => $this->config->password
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $this->config->update([
                    'access_token' => $data['access_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in'])
                ]);

                return ['success' => true, 'data' => $data];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Authentication failed'];
        } catch (\Exception $e) {
            Log::error('HCM Auth Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get valid access token (refresh if needed)
     */
    public function getAccessToken()
    {
        if (!$this->config->isTokenValid()) {
            $result = $this->authenticate();
            if (!$result['success']) {
                return null;
            }
        }

        return $this->config->access_token;
    }

    /**
     * Submit invoice to HCM API
     */
    public function submitInvoice($invoiceData)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->config->api_url . '/api/validate', $invoiceData);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Invoice submission failed',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('HCM Invoice Submit Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send ping to monitor POS status
     */
    public function sendPing()
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Failed to obtain access token'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])
                ->post($this->config->api_url . '/api/ping', [
                    'posId' => $this->config->pos_id,
                    'status' => 'Online'
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => 'Ping failed', 'status_code' => $response->status()];
        } catch (\Exception $e) {
            Log::error('HCM Ping Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        $authResult = $this->authenticate();
        
        if (!$authResult['success']) {
            return $authResult;
        }

        $pingResult = $this->sendPing();
        
        return [
            'success' => $authResult['success'] && $pingResult['success'],
            'auth' => $authResult,
            'ping' => $pingResult
        ];
    }
}
