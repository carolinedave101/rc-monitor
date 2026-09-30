<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Settings
{
    public static function get(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
