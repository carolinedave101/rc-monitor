<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;

class AlertEngine
{
    public function evaluateMessage(Device $device, DeviceMessage $message): void
    {
        $rules = AlertRule::query()
            ->where('type', 'keyword')
            ->where('enabled', true)
            ->where(function ($q) use ($device) {
                $q->whereNull('device_id')->orWhere('device_id', $device->id);
            })
            ->get();

        foreach ($rules as $rule) {
            if ($rule->keyword && str_contains(mb_strtolower($message->body), mb_strtolower($rule->keyword))) {
                Alert::create([
                    'user_id' => $rule->user_id,
                    'device_id' => $device->id,
                    'type' => 'keyword',
                    'severity' => $rule->severity,
                    'title' => 'Keyword match: "'.$rule->keyword.'"',
                    'body' => 'Message from "'.($message->contact_name ?? $message->phone_number ?? 'unknown').'" on '.strtoupper($message->platform).' ('.now()->diffForHumans().'): '.mb_substr($message->body, 0, 200),
                ]);
            }
        }
    }

    public function evaluateLocation(Device $device, DeviceLocation $location): void
    {
        $rules = AlertRule::query()
            ->where('type', 'geofence')
            ->where('enabled', true)
            ->where(function ($q) use ($device) {
                $q->whereNull('device_id')->orWhere('device_id', $device->id);
            })
            ->get();

        foreach ($rules as $rule) {
            if ($rule->latitude === null || $rule->longitude === null) {
                continue;
            }

            $distance = $this->haversine(
                $location->latitude,
                $location->longitude,
                $rule->latitude,
                $rule->longitude
            );

            if ($distance > $rule->radius_meters) {
                Alert::create([
                    'user_id' => $rule->user_id,
                    'device_id' => $device->id,
                    'type' => 'geofence',
                    'severity' => $rule->severity,
                    'title' => 'Device left safe zone',
                    'body' => $device->name.' moved about '.round($distance).' m outside the geofence at ('.$rule->latitude.', '.$rule->longitude.'). Last seen near ('.$location->latitude.', '.$location->longitude.').',
                ]);
                $rule->update(['enabled' => false]);
            }
        }
    }

    protected function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}