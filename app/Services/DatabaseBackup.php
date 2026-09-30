<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackup
{
    public const DIRECTORY = 'backups';

    public function disk(): Filesystem
    {
        return Storage::disk('local');
    }

    /**
     * @return array{path: string, deleted: int}
     */
    public function run(int $keep = 7): array
    {
        $driver = config('database.default');

        $path = match ($driver) {
            'sqlite' => $this->backupSqlite(),
            'mysql', 'mariadb' => $this->backupMysql(),
            'pgsql' => $this->backupPostgres(),
            default => throw new RuntimeException("Unsupported database driver [{$driver}]."),
        };

        return [
            'path' => $path,
            'deleted' => $this->prune($keep),
        ];
    }

    /**
     * @return Collection<int, array{name: string, size: int, modified: int}>
     */
    public function files(): Collection
    {
        $disk = $this->disk();

        if (! $disk->exists(self::DIRECTORY)) {
            return collect();
        }

        return collect($disk->files(self::DIRECTORY))
            ->map(fn (string $file) => [
                'name' => basename($file),
                'size' => $disk->size($file),
                'modified' => $disk->lastModified($file),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    public function prune(int $keep): int
    {
        $files = $this->files();
        $deleted = 0;

        foreach ($files->slice(max(0, $keep)) as $file) {
            $this->disk()->delete(self::DIRECTORY.'/'.$file['name']);
            $deleted++;
        }

        return $deleted;
    }

    protected function backupSqlite(): string
    {
        $source = config('database.connections.sqlite.database');

        if (! is_string($source) || ! is_file($source)) {
            throw new RuntimeException('SQLite database file not found at ['.$source.'].');
        }

        $path = self::DIRECTORY.'/'.$this->filename('sqlite');

        $this->disk()->put($path, file_get_contents($source));

        return $path;
    }

    protected function backupMysql(): string
    {
        $connection = config('database.connections.mysql');

        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --user=%s --host=%s --port=%s %s',
            escapeshellarg((string) ($connection['password'] ?? '')),
            escapeshellarg((string) ($connection['username'] ?? '')),
            escapeshellarg((string) ($connection['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($connection['port'] ?? '3306')),
            escapeshellarg((string) ($connection['database'] ?? '')),
        );

        return $this->runDump($command, 'sql');
    }

    protected function backupPostgres(): string
    {
        $connection = config('database.connections.pgsql');

        $command = sprintf(
            'PGPASSWORD=%s pg_dump --username=%s --host=%s --port=%s %s',
            escapeshellarg((string) ($connection['password'] ?? '')),
            escapeshellarg((string) ($connection['username'] ?? '')),
            escapeshellarg((string) ($connection['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($connection['port'] ?? '5432')),
            escapeshellarg((string) ($connection['database'] ?? '')),
        );

        return $this->runDump($command, 'sql');
    }

    protected function runDump(string $command, string $extension): string
    {
        exec($command.' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Database dump failed: '.implode("\n", array_slice($output, 0, 5)));
        }

        $path = self::DIRECTORY.'/'.$this->filename($extension);

        $this->disk()->put($path, implode("\n", $output));

        return $path;
    }

    protected function filename(string $extension): string
    {
        return 'db-'.now()->format('Ymd-His').'.'.$extension;
    }
}
