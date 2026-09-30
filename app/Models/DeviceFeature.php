<?php

namespace App\Models;

use Database\Factories\DeviceFeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceFeature extends Model
{
    /** @use HasFactory<DeviceFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'feature_id',
        'enabled',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'last_sync_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
