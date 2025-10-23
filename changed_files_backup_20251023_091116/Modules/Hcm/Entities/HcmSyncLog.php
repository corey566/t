
<?php

namespace Modules\Hcm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HcmSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'sync_type',
        'operation_type',
        'data',
        'details',
        'created_by'
    ];

    protected $casts = [
        'data' => 'array',
        'details' => 'array'
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return \Modules\Hcm\Database\factories\HcmSyncLogFactory::new();
    }
}
