<?php

namespace App\Models;

use Database\Factories\DeviceCalendarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCalendarEvent extends Model
{
    /** @use HasFactory<DeviceCalendarEventFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'title',
        'location',
        'starts_at',
        'ends_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
