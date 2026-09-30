<?php

namespace Tests\Feature;

use App\Services\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_command_creates_a_backup_and_prunes_old_ones()
    {
        Storage::fake('local');

        $base = tempnam(sys_get_temp_dir(), 'rcmon');
        $sqlite = $base.'.sqlite';
        rename($base, $sqlite);
        file_put_contents($sqlite, 'SQLite format 3 fake database');
        config(['database.connections.sqlite.database' => $sqlite]);

        foreach (range(1, 4) as $index) {
            Storage::disk('local')->put("backups/db-2026010{$index}-000000.sqlite", 'x');
        }

        $this->artisan('backup:database', ['--keep' => 3])
            ->expectsOutputToContain('Backup written')
            ->assertSuccessful();

        $this->assertCount(3, app(DatabaseBackup::class)->files());

        @unlink($sqlite);
    }

    public function test_backup_command_fails_gracefully_when_the_source_is_missing()
    {
        Storage::fake('local');
        config(['database.connections.sqlite.database' => '/tmp/rc-monitor-missing-database.sqlite']);

        $this->artisan('backup:database')->assertFailed();

        $this->assertCount(0, app(DatabaseBackup::class)->files());
    }
}
