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
            ->where('status', 'active')
            ->first();

        if (! $device) {
            return response()->json(['message' => 'Invalid or suspended agent token.'], 401);
        }

        $device->forceFill(['last_seen_at' => now()])->save();

        $request->merge(['device' => $device]);

        return $next($request);
    }
}