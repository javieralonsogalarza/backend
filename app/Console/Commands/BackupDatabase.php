<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a backup of the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $directory = storage_path('app/backups');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = 'backup-' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $path = $directory . '/' . $filename;

            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg(env('DB_USERNAME')),
                escapeshellarg(env('DB_PASSWORD')),
                escapeshellarg('68.178.132.165'),
                escapeshellarg(env('DB_DATABASE')),
                escapeshellarg($path)
            );

            $process = Process::fromShellCommandline($command);
            $process->run();

            // Log the command and its output
            Log::info('Command executed: ' . $command);
            Log::info('Command output: ' . $process->getOutput());
            Log::info('Command error output: ' . $process->getErrorOutput());

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // Check and maintain a maximum of 30 backup files
            $this->maintainBackupFiles($directory);
            $this->info('Backup was successful.');
            Log::info('Backup was successful.');
        } catch (ProcessFailedException $e) {
            $this->error('Backup failed.');
            $this->error($e->getMessage());
            Log::error('Backup failed.');
            Log::error($e->getMessage());
        } catch (Exception $e) {
            $this->error('An error occurred during the backup process.');
            $this->error($e->getMessage());
            Log::error('An error occurred during the backup process.');
            Log::error($e->getMessage());
        }
    }
    
    
    private function maintainBackupFiles($directory)
    {
        $files = glob($directory . '/*.sql');
        if (count($files) > 30) {
            // Sort files by modification time, oldest first
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Delete the oldest files until we have only 30 left
            while (count($files) > 30) {
                $oldestFile = array_shift($files);
                unlink($oldestFile);
                Log::info('Deleted old backup file: ' . $oldestFile);
            }
        }
    }
}