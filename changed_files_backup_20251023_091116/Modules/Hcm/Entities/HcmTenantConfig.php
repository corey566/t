
<?php

namespace Modules\Hcm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HcmTenantConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'location_id',
        'tenant_id',
        'tenant_secret',
        'api_url',
        'pos_id',
        'stall_no',
        'active',
        'auto_sync',
        'retry_attempts',
        'additional_settings'
    ];

    protected $casts = [
        'active' => 'boolean',
        'auto_sync' => 'boolean',
        'additional_settings' => 'array'
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    protected static function newFactory()
    {
        return \Modules\Hcm\Database\factories\HcmTenantConfigFactory::new();
    }
}
