<?php

namespace App\Models;

use Database\Factories\DeviceAppActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAppActivity extends Model
{
    /** @use HasFactory<DeviceAppActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'app_name',
        'package',
        'category',
        'duration_seconds',
        'launched_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'launched_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
