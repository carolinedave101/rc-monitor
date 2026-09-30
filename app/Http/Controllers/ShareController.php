<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Consent;
use App\Models\Device;
use App\Models\DeviceShare;
use App\Models\User;
use App\Notifications\ShareAccepted;
use App\Notifications\ShareInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $invitations = DeviceShare::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($user) {
                $query->where('viewer_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->whereNull('viewer_id')->where('email', $user->email);
                    });
            })
            ->with(['device', 'owner'])
            ->get();

        $sharedWithMe = DeviceShare::query()
            ->where('viewer_id', $user->id)
            ->where('status', 'accepted')
            ->with(['device', 'owner'])
            ->get();

        $outgoing = DeviceShare::query()
            ->where('owner_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['device', 'viewer'])
            ->get();

        return view('shares.index', compact('invitations', 'sharedWithMe', 'outgoing'));
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        if (strcasecmp($data['email'], $request->user()->email) === 0) {
            return back()->withErrors(['email' => 'You cannot share a device with yourself.']);
        }

        $alreadyShared = $device->shares()
            ->where('email', $data['email'])
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($alreadyShared) {
            return back()->withErrors(['email' => 'This person already has access or a pending invitation.']);
        }

        $viewer = User::where('email', $data['email'])->first();

        $share = $device->shares()->create([
            'owner_id' => $request->user()->id,
            'viewer_id' => $viewer?->id,
            'email' => $data['email'],
            'status' => 'pending',
            'invited_by' => $request->user()->id,
        ]);

        AuditLog::record('share.invited', $share, [
            'device_id' => $device->id,
            'email' => $data['email'],
        ]);

        $viewer?->notify(new ShareInvited($share->load('device', 'owner')));

        return back()->with('status', "Invitation sent to {$data['email']}.");
    }

    public function accept(Request $request, DeviceShare $share): RedirectResponse
    {
        $user = $request->user();

        $isInvitee = $share->viewer_id === $user->id
            || ($share->viewer_id === null && strcasecmp($share->email, $user->email) === 0);

        abort_unless($share->status === 'pending' && $isInvitee, 403);

        $share->update([
            'viewer_id' => $user->id,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        Consent::create([
            'user_id' => $user->id,
            'device_id' => $share->device_id,
            'device_share_id' => $share->id,
            'type' => 'sharing',
            'method' => 'in_app',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'consented_at' => now(),
        ]);

        AuditLog::record('share.accepted', $share, ['viewer_id' => $user->id]);

        $share->owner->notify(new ShareAccepted($share->fresh()->load('viewer', 'device')));

        return redirect()->route('shares.index')
            ->with('status', 'Sharing accepted and consent recorded. The device now appears under "Shared with you".');
    }

    public function revoke(Request $request, DeviceShare $share): RedirectResponse
    {
        $user = $request->user();

        $isInvitee = $share->viewer_id === $user->id
            || ($share->viewer_id === null && strcasecmp($share->email, $user->email) === 0);

        abort_unless($user->id === $share->owner_id || $isInvitee, 403);

        $share->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        AuditLog::record('share.revoked', $share, ['by' => $user->id]);

        return back()->with('status', 'Sharing revoked.');
    }
}
