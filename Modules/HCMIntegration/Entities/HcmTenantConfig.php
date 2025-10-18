
<?php

namespace Modules\HCMIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Business;
use App\BusinessLocation;

class HcmTenantConfig extends Model
{
    protected $fillable = [
        'business_id',
        'location_id',
        'username',
        'password',
        'stall_no',
        'pos_id',
        'api_url',
        'access_token',
        'token_expires_at',
        'is_active',
        'auto_sync'
    ];

    protected $hidden = ['password', 'access_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_sync' => 'boolean',
        'token_expires_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class);
    }

    public function invoiceLogs()
    {
        return $this->hasMany(HcmInvoiceLog::class, 'config_id');
    }

    public function pingLogs()
    {
        return $this->hasMany(HcmPingLog::class, 'config_id');
    }

    public function isTokenValid()
    {
        if (!$this->access_token || !$this->token_expires_at) {
            return false;
        }
        
        return $this->token_expires_at->gt(now()->addSeconds(config('hcmintegration.token_refresh_buffer', 60)));
    }
}
