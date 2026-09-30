<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Missing agent token.'], 401);
        }

        $device = Device::where('agent_token', $token)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if (! $device || ! $device->consent_recorded) {
            return response()->json(['message' => 'Invalid, unconsented or suspended agent token.'], 401);
        }

        $attributes = ['last_seen_at' => now()];

        if ($device->status === 'pending') {
            $attributes['status'] = 'active';
            $attributes['consented_at'] = $device->consented_at ?? now();
        }

        $device->forceFill($attributes)->save();

        $request->merge(['device' => $device]);

        return $next($request);
    }
}
