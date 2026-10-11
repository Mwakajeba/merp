<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    protected $backupPath = 'backups';
    protected $tempPath = 'temp';

    public function __construct()
    {
        // Create backup directories if they don't exist
        $backupDir = storage_path("app/{$this->backupPath}");
        $tempDir = storage_path("app/{$this->tempPath}");

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Create a database backup
     */
    public function createDatabaseBackup($description = null, $backup = null)
    {
        try {
            $filename = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');

            // Create mysqldump command with flags to ensure data is included
            // --single-transaction: ensures consistency for InnoDB tables
            // --routines: includes stored procedures and functions
            // --triggers: includes triggers
            // --quick: forces mysqldump to retrieve rows one at a time
            // --lock-tables=false: don't lock tables (use with --single-transaction)
            // --no-tablespaces: avoid tablespace issues if user lacks SUPER privilege
            $command = "mysqldump -h {$host} -P {$port} -u {$username}";
            if ($password) {
                // Use environment variable for password to avoid shell injection
                putenv("MYSQL_PWD={$password}");
                $command .= " -p";
            }
            $command .= " --single-transaction --routines --triggers --quick " .
                       "--lock-tables=false --no-tablespaces {$database} > {$fullPath}";

            // Clear password from environment after use
            if ($password) {
                putenv("MYSQL_PWD=");
            }

            // Execute the command
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Update existing backup record or create new one
            if ($backup) {
                $backup->update([
                    'name' => 'Database Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'size' => $size,
                    'status' => 'completed',
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Database Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'type' => 'database',
                    'size' => $size,
                    'description' => $description,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            return $backup;
        } catch (\Exception $e) {
            // Update existing backup record or create failed backup record
            if ($backup) {
                $backup->update([
                    'status' => 'failed',
                    'description' => ($backup->description ?? '') . ' (Failed: ' . $e->getMessage() . ')'
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Database Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename ?? 'failed_backup.sql',
                    'file_path' => $filePath ?? 'failed_backup.sql',
                    'type' => 'database',
                    'size' => 0,
                    'description' => $description,
                    'status' => 'failed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Create a files backup
     */
    public function createFilesBackup($description = null, $backup = null)
    {
        try {
            $filename = 'files_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");
            $tempZipPath = storage_path("app/{$this->tempPath}/temp_files.zip");

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Could not create ZIP file');
            }

            // Add files to ZIP
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');
            $this->addDirectoryToZip($zip, public_path('uploads'), 'public/uploads');

            $zip->close();

            // Move to final location
            if (!Storage::move($this->tempPath . '/temp_files.zip', $filePath)) {
                // Fallback to copy and delete
                Storage::copy($this->tempPath . '/temp_files.zip', $filePath);
                Storage::delete($this->tempPath . '/temp_files.zip');
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Update existing backup record or create new one
            if ($backup) {
                $backup->update([
                    'name' => 'Files Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'size' => $size,
                    'status' => 'completed',
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Files Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'type' => 'files',
                    'size' => $size,
                    'description' => $description,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            return $backup;
        } catch (\Exception $e) {
            // Update existing backup record or create failed backup record
            if ($backup) {
                $backup->update([
                    'status' => 'failed',
                    'description' => ($backup->description ?? '') . ' (Failed: ' . $e->getMessage() . ')'
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Files Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename ?? 'failed_backup.zip',
                    'file_path' => $filePath ?? 'failed_backup.zip',
                    'type' => 'files',
                    'size' => 0,
                    'description' => $description,
                    'status' => 'failed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup($description = null, $backup = null)
    {
        try {
            $filename = 'full_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");
            $tempZipPath = storage_path("app/{$this->tempPath}/temp_full.zip");

            // Create database backup first (but don't save to database yet)
            $dbFilename = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $dbFilePath = storage_path("app/{$this->backupPath}/{$dbFilename}");

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');

            // Create mysqldump command with flags to ensure data is included
            $command = "mysqldump -h {$host} -P {$port} -u {$username}";
            if ($password) {
                // Use environment variable for password to avoid shell injection
                putenv("MYSQL_PWD={$password}");
                $command .= " -p";
            }
            $command .= " --single-transaction --routines --triggers --quick " .
                       "--lock-tables=false --no-tablespaces {$database} > {$dbFilePath}";

            // Clear password from environment after use
            if ($password) {
                putenv("MYSQL_PWD=");
            }

            // Execute the command
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Could not create ZIP file');
            }

            // Add database backup to ZIP
            if (File::exists($dbFilePath)) {
                $zip->addFile($dbFilePath, 'database/' . $dbFilename);
            }

            // Add files to ZIP
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');
            $this->addDirectoryToZip($zip, public_path('uploads'), 'public/uploads');

            $zip->close();

            // Move to final location
            if (!Storage::move($this->tempPath . '/temp_full.zip', $filePath)) {
                // Fallback to copy and delete
                Storage::copy($this->tempPath . '/temp_full.zip', $filePath);
                Storage::delete($this->tempPath . '/temp_full.zip');
            }

            // Delete temporary database backup file
            if (file_exists($dbFilePath)) {
                unlink($dbFilePath);
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Update existing backup record or create new one
            if ($backup) {
                $backup->update([
                    'name' => 'Full Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'size' => $size,
                    'status' => 'completed',
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Full Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'type' => 'full',
                    'size' => $size,
                    'description' => $description,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            return $backup;
        } catch (\Exception $e) {
            // Update existing backup record or create failed backup record
            if ($backup) {
                $backup->update([
                    'status' => 'failed',
                    'description' => ($backup->description ?? '') . ' (Failed: ' . $e->getMessage() . ')'
                ]);
            } else {
                $backup = Backup::create([
                    'name' => 'Full Backup - ' . date('Y-m-d H:i:s'),
                    'filename' => $filename ?? 'failed_backup.zip',
                    'file_path' => $filePath ?? 'failed_backup.zip',
                    'type' => 'full',
                    'size' => 0,
                    'description' => $description,
                    'status' => 'failed',
                    'created_by' => auth()->id(),
                    'company_id' => current_company_id(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Backup $backup)
    {
        try {
            $filePath = storage_path("app/{$backup->file_path}");

            if (!file_exists($filePath)) {
                throw new \Exception('Backup file not found');
            }

            if ($backup->type === 'database') {
                return $this->restoreDatabaseBackup($filePath);
            } elseif ($backup->type === 'files') {
                return $this->restoreFilesBackup($filePath);
            } elseif ($backup->type === 'full') {
                return $this->restoreFullBackup($filePath);
            }

            throw new \Exception('Unsupported backup type');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabaseBackup($filePath)
    {
        $this->importSqlIntoLocalDatabase($filePath);

        return true;
    }

    /**
     * SQL dumps that can be imported without going through the PHP upload limit.
     */
    public function availableSqlImports(): array
    {
        $directory = storage_path('app/sql-imports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $files = [];
        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();
            if (!preg_match('/\.sql(\.gz)?$/i', $name)) {
                continue;
            }

            $files[] = [
                'name' => $name,
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            ];
        }

        usort($files, fn (array $a, array $b) => $b['modified'] <=> $a['modified']);

        return $files;
    }

    /**
     * Resolve a dump placed in storage/app/sql-imports.
     */
    public function resolveSqlImportFile(string $filename): string
    {
        $directory = storage_path('app/sql-imports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $basename = basename($filename);
        if (!preg_match('/\.sql(\.gz)?$/i', $basename)) {
            throw new \InvalidArgumentException('Only .sql and .sql.gz files can be imported.');
        }

        $directoryReal = realpath($directory);
        $fileReal = realpath($directory . DIRECTORY_SEPARATOR . $basename);
        if ($directoryReal === false || $fileReal === false || !str_starts_with($fileReal, $directoryReal . DIRECTORY_SEPARATOR)) {
            throw new \InvalidArgumentException('SQL file was not found in storage/app/sql-imports.');
        }

        return $fileReal;
    }

    /**
     * Import a .sql or .sql.gz dump into the local database.
     * CREATE DATABASE and USE statements are skipped so an online dump
     * lands in this installation's database.
     */
    public function importSqlIntoLocalDatabase(string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new \Exception('SQL file not found.');
        }

        $database = (string) config('database.connections.mysql.database');
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');
        $host = (string) config('database.connections.mysql.host');
        $port = (string) config('database.connections.mysql.port');

        if ($database === '' || $username === '' || $host === '') {
            throw new \Exception('Local database connection is not configured.');
        }

        $configFile = storage_path('app/temp/mysql-import-' . Str::uuid() . '.cnf');
        File::ensureDirectoryExists(dirname($configFile));
        File::put($configFile, implode("\n", [
            '[client]',
            'host=' . $this->mysqlConfigValue($host),
            'port=' . (int) $port,
            'user=' . $this->mysqlConfigValue($username),
            'password=' . $this->mysqlConfigValue($password),
            '',
        ]));
        chmod($configFile, 0600);

        $streamPath = str_ends_with(strtolower($filePath), '.gz')
            ? 'compress.zlib://' . $filePath
            : $filePath;
        $input = fopen($streamPath, 'rb');
        if ($input === false) {
            File::delete($configFile);
            throw new \Exception('Could not read the SQL file.');
        }

        $command = sprintf(
            'mysql --defaults-extra-file=%s --binary-mode %s',
            escapeshellarg($configFile),
            escapeshellarg($database)
        );

        $errorLog = storage_path('app/temp/mysql-import-' . Str::uuid() . '.log');
        $process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', $errorLog, 'w'],
        ], $pipes);

        if (!is_resource($process)) {
            fclose($input);
            File::delete($configFile);
            throw new \Exception('Could not start the mysql client. Confirm the mysql command is installed.');
        }

        $writeError = null;
        try {
            $this->writeToMysqlPipe($pipes[0], "SET FOREIGN_KEY_CHECKS=0;\nSET UNIQUE_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");

            while (!feof($input)) {
                $line = fgets($input);
                if ($line === false) {
                    break;
                }

                $trimmed = ltrim($line);
                if (preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $trimmed)) {
                    continue;
                }

                $this->writeToMysqlPipe($pipes[0], $line);
            }

            $this->writeToMysqlPipe($pipes[0], "\nSET FOREIGN_KEY_CHECKS=1;\nSET UNIQUE_CHECKS=1;\n");
        } catch (\Exception $e) {
            $writeError = $e;
        } finally {
            fclose($input);
            if (isset($pipes[0]) && is_resource($pipes[0])) {
                fclose($pipes[0]);
            }
        }

        $exitCode = proc_close($process);
        $errorOutput = File::exists($errorLog) ? File::get($errorLog) : '';
        File::delete([$configFile, $errorLog]);

        if ($exitCode !== 0) {
            $message = trim((string) $errorOutput);
            throw new \Exception($message !== '' ? 'Database import failed: ' . $message : 'Database import failed.');
        }

        if ($writeError) {
            throw $writeError;
        }
    }

    protected function mysqlConfigValue(string $value): string
    {
        return '"' . str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '', ''], $value) . '"';
    }

    protected function writeToMysqlPipe($pipe, string $data): void
    {
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $result = fwrite($pipe, substr($data, $written));
            if ($result === false) {
                throw new \Exception('The mysql client stopped before the SQL file finished importing.');
            }
            $written += $result;
        }
    }

    /**
     * Restore files from ZIP
     */
    protected function restoreFilesBackup($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Could not open ZIP file');
        }

        $zip->extractTo(storage_path('app/'));
        $zip->close();

        return true;
    }

    /**
     * Restore full backup
     */
    protected function restoreFullBackup($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Could not open ZIP file');
        }

        // Extract to temp directory
        $tempExtractPath = storage_path("app/{$this->tempPath}/restore");
        if (File::exists($tempExtractPath)) {
            File::deleteDirectory($tempExtractPath);
        }
        File::makeDirectory($tempExtractPath, 0755, true);

        $zip->extractTo($tempExtractPath);
        $zip->close();

        // Restore database
        $dbFile = $tempExtractPath . '/database/' . File::files($tempExtractPath . '/database')[0];
        $this->restoreDatabaseBackup($dbFile);

        // Restore files
        if (File::exists($tempExtractPath . '/storage')) {
            File::copyDirectory($tempExtractPath . '/storage', storage_path('app/'));
        }
        if (File::exists($tempExtractPath . '/public')) {
            File::copyDirectory($tempExtractPath . '/public', public_path());
        }

        // Clean up
        File::deleteDirectory($tempExtractPath);

        return true;
    }

    /**
     * Add directory to ZIP archive
     */
    protected function addDirectoryToZip($zip, $dirPath, $zipPath)
    {
        if (!File::exists($dirPath)) {
            return;
        }

        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            $relativePath = str_replace($dirPath . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $zipPath . '/' . $relativePath);
        }
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        $companyId = current_company_id();

        return [
            'total' => Backup::forCompany()->count(),
            'database' => Backup::forCompany()->byType('database')->count(),
            'files' => Backup::forCompany()->byType('files')->count(),
            'full' => Backup::forCompany()->byType('full')->count(),
            'completed' => Backup::forCompany()->completed()->count(),
            'failed' => Backup::forCompany()->failed()->count(),
            'total_size' => Backup::forCompany()->completed()->sum('size'),
        ];
    }

    /**
     * Clean old backups
     */
    public function cleanOldBackups($days = 30)
    {
        $oldBackups = Backup::forCompany()
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        foreach ($oldBackups as $backup) {
            $backup->deleteFile();
            $backup->delete();
        }

        return $oldBackups->count();
    }
}
