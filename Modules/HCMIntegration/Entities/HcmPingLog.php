
<?php

namespace Modules\HCMIntegration\Entities;

use Illuminate\Database\Eloquent\Model;

class HcmPingLog extends Model
{
    protected $fillable = [
        'config_id',
        'status',
        'response_data',
        'pinged_at'
    ];

    protected $casts = [
        'pinged_at' => 'datetime'
    ];

    public function config()
    {
        return $this->belongsTo(HcmTenantConfig::class, 'config_id');
    }
}
