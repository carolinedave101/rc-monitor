<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'platform',
        'direction',
        'contact_name',
        'phone_number',
        'body',
        'was_deleted',
        'sent_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'was_deleted' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
