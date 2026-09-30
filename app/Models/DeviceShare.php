<?php

namespace App\Models;

use Database\Factories\DeviceShareFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceShare extends Model
{
    /** @use HasFactory<DeviceShareFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'accepted', 'revoked'];

    protected $fillable = [
        'device_id',
        'owner_id',
        'viewer_id',
        'email',
        'status',
        'invited_by',
        'accepted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
