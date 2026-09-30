<?php

namespace App\Models;

use Database\Factories\DeviceEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEmail extends Model
{
    /** @use HasFactory<DeviceEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'direction',
        'address',
        'subject',
        'snippet',
        'sent_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
