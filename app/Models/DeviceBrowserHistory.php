<?php

namespace App\Models;

use Database\Factories\DeviceBrowserHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceBrowserHistory extends Model
{
    /** @use HasFactory<DeviceBrowserHistoryFactory> */
    use HasFactory;

    protected $table = 'device_browser_histories';

    protected $fillable = [
        'device_id',
        'url',
        'domain',
        'title',
        'visited_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
