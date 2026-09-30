<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    public const STATUSES = ['draft', 'sent', 'awaiting_verification', 'rejected', 'paid', 'void'];

    protected $fillable = [
        'user_id',
        'service_step_id',
        'plan_id',
        'number',
        'status',
        'currency',
        'subtotal_cents',
        'total_cents',
        'due_at',
        'notes',
        'issued_at',
        'paid_at',
        'rejection_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'total_cents' => 'integer',
            'due_at' => 'datetime',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public static function generateNumber(): string
    {
        $next = (int) static::query()->max('id') + 1;

        return 'INV-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceStep(): BelongsTo
    {
        return $this->belongsTo(ServiceStep::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class);
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'awaiting_verification', 'rejected']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function totalLabel(): string
    {
        return '$'.number_format($this->total_cents / 100, 2);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Awaiting payment',
            'awaiting_verification' => 'Verifying payment',
            'rejected' => 'Payment needs attention',
            'paid' => 'Paid',
            'void' => 'Void',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'awaiting_verification' => 'info',
            'rejected' => 'danger',
            'void' => 'dark',
            'draft' => 'secondary',
            default => 'warning',
        };
    }
}
