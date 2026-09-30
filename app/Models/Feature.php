<?php

namespace App\Models;

use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    public const STATUSES = ['live', 'simulated', 'beta', 'coming_soon', 'disabled'];

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'status',
        'is_public',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('name');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('status', '!=', 'disabled');
    }

    public function isAvailable(): bool
    {
        return in_array($this->status, ['live', 'simulated', 'beta'], true);
    }
}
