<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemController extends Controller
{
    public function index(DatabaseBackup $backup): View
    {
        $backups = $backup->files();

        $health = [
            'environment' => app()->environment(),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database' => config('database.default'),
            'queue' => config('queue.default'),
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];

        return view('admin.system.index', compact('backups', 'health'));
    }

    public function backup(Request $request, DatabaseBackup $backup): RedirectResponse
    {
        try {
            $result = $backup->run();
        } catch (\Throwable $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        AuditLog::record('system.backup_created', null, ['path' => $result['path']]);

        return back()->with('status', "Backup created: {$result['path']}");
    }

    public function download(DatabaseBackup $backup, string $filename): StreamedResponse
    {
        $path = DatabaseBackup::DIRECTORY.'/'.basename($filename);

        abort_unless($backup->disk()->exists($path), 404);

        return $backup->disk()->download($path);
    }
}
