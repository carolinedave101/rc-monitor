<?php

namespace App\Models;

use Database\Factories\DeviceMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMedia extends Model
{
    /** @use HasFactory<DeviceMediaFactory> */
    use HasFactory;

    protected $table = 'device_media';

    protected $fillable = [
        'device_id',
        'type',
        'filename',
        'size_mb',
        'taken_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'size_mb' => 'float',
            'taken_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
