<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ServiceStep;
use App\Models\User;
use App\Notifications\JourneyStatusChanged;

class JourneyManager
{
    public function addStep(User $user, array $data): ServiceStep
    {
        $position = (int) $user->serviceSteps()->max('position') + 1;

        $step = $user->serviceSteps()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'requires_payment' => $data['requires_payment'] ?? false,
            'position' => $position,
            'status' => 'pending',
        ]);

        AuditLog::record('journey.step.created', $step, [
            'owner_id' => $user->id,
            'title' => $step->title,
        ]);

        return $step;
    }

    public function start(ServiceStep $step): void
    {
        $step->update(['status' => 'in_progress']);

        AuditLog::record('journey.step.started', $step, ['owner_id' => $step->user_id]);
    }

    public function advance(ServiceStep $step): void
    {
        $step->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $next = $step->user->serviceSteps()
            ->where('status', 'pending')
            ->orderBy('position')
            ->orderBy('id')
            ->first();

        if ($next) {
            $next->update(['status' => 'in_progress']);
        }

        AuditLog::record('journey.step.completed', $step, [
            'owner_id' => $step->user_id,
            'next_step_id' => $next?->id,
        ]);
    }

    public function pause(ServiceStep $step, string $reason, bool $suspendServices): void
    {
        $step->update([
            'paused_at' => now(),
            'paused_reason' => $reason,
            'pause_suspends_services' => $suspendServices,
        ]);

        if ($suspendServices) {
            $step->user->devices()
                ->whereIn('status', ['pending', 'active'])
                ->update(['status' => 'suspended', 'pause_suspended' => true]);
        }

        AuditLog::record('journey.step.paused', $step, [
            'owner_id' => $step->user_id,
            'reason' => $reason,
            'services_suspended' => $suspendServices,
        ]);

        $step->user->notify(new JourneyStatusChanged($step, 'paused'));
    }

    public function resume(ServiceStep $step): void
    {
        $wasSuspendingServices = $step->pause_suspends_services;
        $ownerId = $step->user_id;

        $step->update([
            'paused_at' => null,
            'paused_reason' => null,
            'pause_suspends_services' => false,
        ]);

        if ($wasSuspendingServices) {
            $step->user->devices()
                ->where('pause_suspended', true)
                ->update(['status' => 'active', 'pause_suspended' => false]);
        }

        AuditLog::record('journey.step.resumed', $step, ['owner_id' => $ownerId]);

        $step->user->notify(new JourneyStatusChanged($step, 'resumed'));
    }

    public function remove(ServiceStep $step): void
    {
        AuditLog::record('journey.step.deleted', $step, [
            'owner_id' => $step->user_id,
            'title' => $step->title,
        ]);

        $step->delete();
    }
}
