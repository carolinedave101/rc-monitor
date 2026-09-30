<?php

namespace App\Models;

use Database\Factories\DeviceNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceNote extends Model
{
    /** @use HasFactory<DeviceNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'title',
        'body',
        'source',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
