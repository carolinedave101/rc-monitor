<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'manufacturer',
        'model',
        'os',
        'os_version',
        'phone_number',
        'status',
        'consent_recorded',
        'consented_at',
        'agent_token',
        'last_seen_at',
        'source',
        'pause_suspended',
    ];

    protected function casts(): array
    {
        return [
            'consent_recorded' => 'boolean',
            'consented_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'pause_suspended' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(DeviceCall::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DeviceMessage::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(DeviceLocation::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function appActivities(): HasMany
    {
        return $this->hasMany(DeviceAppActivity::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DeviceContact::class);
    }

    public function diagnostics(): HasMany
    {
        return $this->hasMany(DeviceDiagnostic::class);
    }

    public function browserHistories(): HasMany
    {
        return $this->hasMany(DeviceBrowserHistory::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(DeviceEmail::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(DeviceMedia::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DeviceNote::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(DeviceCalendarEvent::class);
    }

    public function featureStates(): HasMany
    {
        return $this->hasMany(DeviceFeature::class);
    }

    public function simulationProfile(): HasOne
    {
        return $this->hasOne(SimulationProfile::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DeviceShare::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('shares', function (Builder $query) use ($user) {
                    $query->where('viewer_id', $user->id)->where('status', 'accepted');
                });
        });
    }

    public function isSharedWith(User $user): bool
    {
        return $this->shares()
            ->where('viewer_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at?->gt(now()->subMinutes(5)) ?? false;
    }
}
