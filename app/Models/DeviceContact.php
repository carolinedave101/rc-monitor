<?php

namespace App\Models;

use Database\Factories\DeviceContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceContact extends Model
{
    /** @use HasFactory<DeviceContactFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'phone_number',
        'email',
        'source',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
