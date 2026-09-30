<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'direction',
        'contact_name',
        'phone_number',
        'duration_seconds',
        'started_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'started_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
