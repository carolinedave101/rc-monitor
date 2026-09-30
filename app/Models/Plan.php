<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_cents',
        'currency',
        'billing_type',
        'device_limit',
        'is_active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'device_limit' => 'integer',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('name');
    }

    public function priceLabel(): string
    {
        return '$'.number_format($this->price_cents / 100, 2);
    }
}
