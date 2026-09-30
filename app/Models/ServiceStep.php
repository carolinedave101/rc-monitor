<?php

namespace App\Models;

use Database\Factories\ServiceStepFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceStep extends Model
{
    /** @use HasFactory<ServiceStepFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'in_progress', 'awaiting_payment', 'awaiting_verification', 'completed'];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'position',
        'status',
        'requires_payment',
        'paused_at',
        'paused_reason',
        'pause_suspends_services',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'requires_payment' => 'boolean',
            'pause_suspends_services' => 'boolean',
            'paused_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function outstandingInvoice(): ?Invoice
    {
        return $this->invoices()->outstanding()->latest()->first();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In progress',
            'awaiting_payment' => 'Awaiting payment',
            'awaiting_verification' => 'Verifying payment',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'awaiting_payment', 'awaiting_verification' => 'warning',
            default => 'secondary',
        };
    }
}
