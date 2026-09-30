<?php

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    public const TYPES = ['bank_transfer', 'paypal', 'cashapp', 'crypto', 'other'];

    protected $fillable = [
        'label',
        'type',
        'details',
        'enabled',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('label');
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'bank_transfer' => 'Bank transfer',
            'paypal' => 'PayPal',
            'cashapp' => 'CashApp',
            'crypto' => 'Crypto',
            default => ucfirst($this->type),
        };
    }
}
