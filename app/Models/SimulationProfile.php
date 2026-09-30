<?php

namespace App\Models;

use Database\Factories\SimulationProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationProfile extends Model
{
    /** @use HasFactory<SimulationProfileFactory> */
    use HasFactory;

    public const LEVELS = ['low', 'normal', 'high'];

    protected $fillable = [
        'device_id',
        'enabled',
        'activity_level',
        'last_tick_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'last_tick_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
