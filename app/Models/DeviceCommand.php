<?php

namespace App\Models;

use Database\Factories\DeviceCommandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    /** @use HasFactory<DeviceCommandFactory> */
    use HasFactory;

    public const TYPES = ['lock', 'ring', 'locate'];

    public const LABELS = [
        'lock' => 'Lock device',
        'ring' => 'Ring device',
        'locate' => 'Locate now',
    ];

    public const STATUSES = ['pending', 'sent', 'acknowledged', 'failed'];

    protected $fillable = [
        'device_id',
        'requested_by',
        'type',
        'status',
        'payload',
        'issued_at',
        'sent_at',
        'acknowledged_at',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'issued_at' => 'datetime',
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function label(): string
    {
        return self::LABELS[$this->type] ?? ucfirst($this->type);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'acknowledged' => 'success',
            'failed' => 'danger',
            'sent' => 'info',
            default => 'secondary',
        };
    }
}
