<?php

namespace Modules\Gallface\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\BusinessLocation;

class LocationApiCredential extends Model
{
    use HasFactory;

    protected $table = 'location_api_credentials';

    protected $fillable = [
        'business_id',
        'business_location_id',
        'mall_code',
        'api_url',
        'api_key',
        'access_token_url',
        'production_url',
        'client_id',
        'client_secret',
        'username',
        'password',
        'property_code',
        'pos_interface_code',
        'stall_no',
        'pos_id',
        'sync_type',
        'ping_interval',
        'auto_sync_enabled',
        'additional_data',
        'is_active',
        'last_synced_at',
        'last_ping_at'
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'last_ping_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_sync_enabled' => 'boolean'
    ];

    public function businessLocation()
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }

    public function getCredentialsForApi()
    {
        $credentials = [
            'api_url' => $this->api_url,
            'username' => $this->username,
            'password' => $this->password,
            'ping_interval' => $this->ping_interval ?? 5
        ];

        if ($this->mall_code === 'hcm') {
            $credentials['stall_no'] = $this->stall_no;
            $credentials['pos_id'] = $this->pos_id;
        } else {
            $credentials['api_key'] = $this->api_key;
            $credentials['client_id'] = $this->client_id;
            $credentials['client_secret'] = $this->client_secret;
            $credentials['access_token_url'] = $this->access_token_url;
            $credentials['production_url'] = $this->production_url;
            $credentials['property_code'] = $this->property_code;
            $credentials['pos_interface_code'] = $this->pos_interface_code;
        }

        return $credentials;
    }

    public function hasCompleteCredentials()
    {
        if ($this->mall_code === 'hcm') {
            return !empty($this->api_url) &&
                   !empty($this->username) &&
                   !empty($this->password) &&
                   !empty($this->stall_no) &&
                   !empty($this->pos_id);
        }

        return !empty($this->api_url) &&
               !empty($this->api_key) &&
               !empty($this->client_id) &&
               !empty($this->client_secret);
    }
}