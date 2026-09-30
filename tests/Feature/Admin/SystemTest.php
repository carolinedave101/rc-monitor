<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeSqliteFile(): string
    {
        $base = tempnam(sys_get_temp_dir(), 'rcmon');
        $path = $base.'.sqlite';
        rename($base, $path);
        file_put_contents($path, 'SQLite format 3 fake database');

        config(['database.connections.sqlite.database' => $path]);

        return $path;
    }

    public function test_non_admin_cannot_access_system()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/system')->assertForbidden();
        $this->actingAs($user)->post('/admin/system/backup')->assertForbidden();
    }

    public function test_admin_sees_system_page_with_health_and_backups()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/system')
            ->assertOk()
            ->assertSee('Health')
            ->assertSee('Backups')
            ->assertSee(PHP_VERSION);
    }

    public function test_admin_can_run_a_backup_and_it_is_audited()
    {
        Storage::fake('local');
        $sqlite = $this->fakeSqliteFile();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/system/backup')
            ->assertRedirect();

        $files = app(DatabaseBackup::class)->files();
        $this->assertCount(1, $files);
        $this->assertStringStartsWith('db-', $files->first()['name']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'system.backup_created']);

        @unlink($sqlite);
    }

    public function test_admin_can_download_a_backup()
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/db-20260101-000000.sqlite', 'backup-data');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/system/backup/db-20260101-000000.sqlite')
            ->assertOk()
            ->assertDownload('db-20260101-000000.sqlite');
    }

    public function test_missing_backup_returns_404()
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/system/backup/db-nope.sqlite')
            ->assertNotFound();
    }

    public function test_prune_keeps_only_the_requested_number_of_backups()
    {
        Storage::fake('local');

        foreach (range(1, 5) as $index) {
            Storage::disk('local')->put("backups/db-2026010{$index}-000000.sqlite", 'x');
        }

        $deleted = app(DatabaseBackup::class)->prune(2);

        $this->assertSame(3, $deleted);
        $this->assertCount(2, app(DatabaseBackup::class)->files());
    }
}
