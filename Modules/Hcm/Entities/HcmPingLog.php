
<?php

namespace Modules\Hcm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HcmPingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'location_id',
        'status',
        'response_data',
        'response_message',
        'last_ping_at'
    ];

    protected $casts = [
        'response_data' => 'array',
        'last_ping_at' => 'datetime'
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
        return \Modules\Hcm\Database\factories\HcmPingLogFactory::new();
    }
}
