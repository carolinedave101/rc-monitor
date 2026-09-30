<?php

namespace App\Models;

use Database\Factories\DeviceDiagnosticFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceDiagnostic extends Model
{
    /** @use HasFactory<DeviceDiagnosticFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'battery_percent',
        'is_charging',
        'storage_used_mb',
        'storage_total_mb',
        'network',
        'recorded_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'battery_percent' => 'integer',
            'is_charging' => 'boolean',
            'storage_used_mb' => 'integer',
            'storage_total_mb' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
