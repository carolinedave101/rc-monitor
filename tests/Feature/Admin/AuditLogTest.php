<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_records_actor_action_and_subject()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->for($admin)->create();

        $this->actingAs($admin);

        $log = AuditLog::record('device.status.updated', $device, ['from' => 'active', 'to' => 'suspended']);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'user_id' => $admin->id,
            'action' => 'device.status.updated',
            'auditable_type' => Device::class,
            'auditable_id' => $device->id,
        ]);

        $this->assertSame(['from' => 'active', 'to' => 'suspended'], $log->meta);
        $this->assertSame('127.0.0.1', $log->ip_address);
    }

    public function test_audit_log_can_be_recorded_without_a_subject()
    {
        $log = AuditLog::record('admin.login');

        $this->assertNull($log->auditable_type);
        $this->assertNull($log->auditable_id);
        $this->assertNull($log->meta);
    }
}
