<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Exception;

class BackupDatabaseAndStorage extends Command
{
    protected $signature = 'backup:run {--test : Run in test mode}';
    protected $description = 'Create and upload database and storage backups to Google Drive';

    private $googleDriveService;
    protected $backupOutput = [];  // Changed from private $output to protected $backupOutput

    public function handle()
    {

        try {
            $this->info('Starting backup process...');

            // Step 1: Setup Google credentials
            $this->backupOutput[] = "Step 1: Testing Google credentials...";
            $this->setupGoogleClient();
            $this->backupOutput[] = "✓ Google credentials loaded successfully";

            // Step 2: Check database connection
            $this->backupOutput[] = "\nStep 2: Testing database connection...";
            $dbConfig = $this->getDatabaseConfig();
            $this->backupOutput[] = "Database configuration found:";
            $this->backupOutput[] = "- Host: " . $dbConfig['host'];
            $this->backupOutput[] = "- Port: " . $dbConfig['port'];
            $this->backupOutput[] = "- Database: " . $dbConfig['database'];
            $this->backupOutput[] = "- Username: " . $dbConfig['username'];
            $this->backupOutput[] = "✓ Database configuration loaded";

            // Step 3: Check backup directory
            $this->backupOutput[] = "\nStep 3: Testing backup directory...";
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
                $this->backupOutput[] = "Created backup directory: " . $backupDir;
            }
            $this->backupOutput[] = "✓ Backup directory exists and is writable";

            // Step 4: Create database backup
            $this->backupOutput[] = "\nStep 4: Creating database backup...";
            $dbBackupPath = $this->backupDatabase();
            $this->backupOutput[] = "✓ Database backup created at: " . $dbBackupPath;

            // Step 5: Create storage backup
            $this->backupOutput[] = "\nStep 5: Creating storage backup...";
            $storageBackupPath = $this->backupStorage();
            $this->backupOutput[] = "✓ Storage backup created at: " . $storageBackupPath;

            // Step 6: Upload to Google Drive
            $this->backupOutput[] = "\nStep 6: Uploading to Google Drive...";
            $this->uploadToGoogleDrive($dbBackupPath, basename($dbBackupPath));
            $this->uploadToGoogleDrive($storageBackupPath, basename($storageBackupPath));
            $this->backupOutput[] = "✓ Files uploaded to Google Drive successfully";

            // Cleanup if not in test mode
            if (!$this->option('test')) {
                @unlink($dbBackupPath);
                @unlink($storageBackupPath);
                $this->backupOutput[] = "\n✓ Cleanup completed";
            }

            // Output all logs
            foreach ($this->backupOutput as $line) {
                $this->info($line);
            }

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            $this->error('Line: ' . $e->getLine() . ' in ' . $e->getFile());

            // Output logs up to the failure point
            foreach ($this->backupOutput as $line) {
                $this->info($line);
            }

            return Command::FAILURE;
        }
    }

    private function setupGoogleClient()
    {
        $client = new Google_Client();

        $credentialsPath = storage_path('app/google-credentials.json');
        if (!file_exists($credentialsPath)) {
            throw new Exception("Google credentials file not found at: " . $credentialsPath);
        }

        $client->setAuthConfig($credentialsPath);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);

        $this->googleDriveService = new Google_Service_Drive($client);
    }

    private function getDatabaseConfig()
    {
        return [
            'host' => config('database.connections.pgsql.host'),
            'port' => config('database.connections.pgsql.port'),
            'database' => config('database.connections.pgsql.database'),
            'username' => config('database.connections.pgsql.username'),
            'password' => config('database.connections.pgsql.password'),
        ];
    }

    private function backupDatabase()
    {
        // $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.dump';

        $backupPath = storage_path('app/backups/' . $filename);

        $config = $this->getDatabaseConfig();

        // Set PGPASSWORD environment variable
        putenv("PGPASSWORD={$config['password']}");

        // Use PostgreSQL 16 path
        $pgDumpPath = '/usr/bin/pg_dump';
        // $pgDumpPath = '"C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe"';


        // Build the command with proper escaping for Windows
        // $command = "{$pgDumpPath} -h {$config['host']} -p {$config['port']} -U {$config['username']} -F p -b -f \"{$backupPath}\" {$config['database']} 2>&1";
        $command = "{$pgDumpPath} -h {$config['host']} -p {$config['port']} -U {$config['username']} -F c -b -f \"{$backupPath}\" {$config['database']} 2>&1";


        \Log::info('Starting database backup');
        \Log::info('Backup command: ' . $command);

        exec($command, $output, $returnCode);

        \Log::info('Command output: ', $output);
        \Log::info('Return code: ' . $returnCode);

        // Clear PGPASSWORD environment variable
        putenv("PGPASSWORD");

        if ($returnCode !== 0) {
            throw new Exception("Database backup failed: " . implode("\n", $output));
        }

        return $backupPath;
    }

    private function backupStorage()
    {
        $filename = 'storage-' . Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
        $backupPath = storage_path('app/backups/' . $filename);

        $uploadsPath = public_path('uploads');

        $zip = new \ZipArchive();
        if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip archive");
        }

        $uploadsPath = realpath($uploadsPath);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = 'uploads/' . substr($filePath, strlen($uploadsPath) + 1);

                \Log::info("Adding file to zip: " . $relativePath);

                if (!$zip->addFile($filePath, $relativePath)) {
                    throw new Exception("Could not add file to zip: " . $filePath);
                }
            }
        }

        if (!$zip->close()) {
            throw new Exception("Could not create zip file");
        }

        \Log::info("Zip file created at: " . $backupPath);
        return $backupPath;
    }

    private function uploadToGoogleDrive($filePath, $filename)
    {
        try {
            // Your Google Drive folder ID
            $folderId = '19t_HACiXuZ7DmiM6NE53jiKD88-BXX6N'; // Replace with your actual folder ID

            \Log::info("Starting Google Drive upload to folder: " . $folderId);
            \Log::info("File: " . $filePath);

            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $filename,
                'parents' => [$folderId]
            ]);

            $file = $this->googleDriveService->files->create(
                $fileMetadata,
                [
                    'data' => file_get_contents($filePath),
                    'mimeType' => 'application/octet-stream',
                    'uploadType' => 'multipart',
                    'fields' => 'id, name, webViewLink'
                ]
            );

            \Log::info("File uploaded successfully");
            \Log::info("File ID: " . $file->getId());
            \Log::info("View URL: " . $file->getWebViewLink());

            return $file;
        } catch (\Exception $e) {
            \Log::error("Google Drive upload failed: " . $e->getMessage());
            throw $e;
        }
    }
}
