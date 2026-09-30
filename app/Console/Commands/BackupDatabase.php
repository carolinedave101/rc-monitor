<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Number of most recent backups to retain}';

    protected $description = 'Create a database backup and prune old ones';

    public function handle(DatabaseBackup $backup): int
    {
        $keep = max(1, (int) $this->option('keep'));

        try {
            $result = $backup->run($keep);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup written to storage/app/private/{$result['path']}");

        if ($result['deleted'] > 0) {
            $this->line("Pruned {$result['deleted']} old backup(s), keeping the latest {$keep}.");
        }

        return self::SUCCESS;
    }
}
