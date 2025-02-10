<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Exception;

class BackupController extends Controller
{
    private $googleDriveService;

    public function testBackup()
    {
        try {
            $output = [];

            // Step 1: Test Google credentials
            $output[] = "Step 1: Testing Google credentials...";
            $this->setupGoogleClient();
            $output[] = "✓ Google credentials loaded successfully";

            // Step 2: Test database connection
            $output[] = "\nStep 2: Testing database connection...";
            $dbConfig = $this->getDatabaseConfig();
            $output[] = "Database configuration found:";
            $output[] = "- Host: " . $dbConfig['host'];
            $output[] = "- Port: " . $dbConfig['port'];
            $output[] = "- Database: " . $dbConfig['database'];
            $output[] = "- Username: " . $dbConfig['username'];
            $output[] = "✓ Database configuration loaded";

            // Step 3: Test backup directory
            $output[] = "\nStep 3: Testing backup directory...";
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
                $output[] = "Created backup directory: " . $backupDir;
            }
            $output[] = "✓ Backup directory exists and is writable";

            // Step 4: Create test database backup
            $output[] = "\nStep 4: Creating test database backup...";
            $dbBackupPath = $this->backupDatabase();
            $output[] = "✓ Database backup created at: " . $dbBackupPath;

            // Step 5: Create test storage backup
            $output[] = "\nStep 5: Creating test storage backup...";
            $storageBackupPath = $this->backupStorage();
            $output[] = "✓ Storage backup created at: " . $storageBackupPath;

            // Step 6: Test Google Drive upload
            $output[] = "\nStep 6: Testing Google Drive upload...";
// In backupDatabase() method:
$this->uploadToGoogleDrive($dbBackupPath, basename($dbBackupPath));

// In backupStorage() method:
$this->uploadToGoogleDrive($storageBackupPath, basename($storageBackupPath));

            $output[] = "✓ Files uploaded to Google Drive successfully";

            // Cleanup
            @unlink($dbBackupPath);
            @unlink($storageBackupPath);
            $output[] = "\n✓ Cleanup completed";

            return response()->json([
                'success' => true,
                'message' => 'Backup test completed successfully',
                'logs' => $output
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup test failed',
                'error' => $e->getMessage(),
                'logs' => $output ?? [],
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
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
    $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
    $backupPath = storage_path('app/backups/' . $filename);

    $config = $this->getDatabaseConfig();

    // Set PGPASSWORD environment variable
    putenv("PGPASSWORD={$config['password']}");

    // Use your specific PostgreSQL 16 path
    $pgDumpPath = '"C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe"';

    // Build the command with proper escaping for Windows
    $command = "{$pgDumpPath} -h {$config['host']} -p {$config['port']} -U {$config['username']} -F p -b -f \"{$backupPath}\" {$config['database']} 2>&1";

    // Debug logging
    \Log::info('Starting database backup');
    \Log::info('Backup command: ' . $command);

    exec($command, $output, $returnCode);

    // Log the output and return code
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

    // Define path to uploads folder
    $uploadsPath = public_path('uploads');  // Assuming uploads is in public directory

    $zip = new \ZipArchive();
    if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Cannot create zip archive");
    }

    // Get real path for our folder
    $uploadsPath = realpath($uploadsPath);

    // Create recursive directory iterator
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($uploadsPath),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        // Skip directories (they would be added automatically)
        if (!$file->isDir()) {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            // Make relative path by removing uploads path from file path
            $relativePath = 'uploads/' . substr($filePath, strlen($uploadsPath) + 1);

            \Log::info("Adding file to zip: " . $relativePath);  // Debug log

            if (!$zip->addFile($filePath, $relativePath)) {
                throw new Exception("Could not add file to zip: " . $filePath);
            }
        }
    }

    if (!$zip->close()) {
        throw new Exception("Could not create zip file");
    }

    \Log::info("Zip file created at: " . $backupPath);  // Debug log
    return $backupPath;
}

private function uploadToGoogleDrive($filePath, $filename)
{
    try {
        // Your Google Drive folder ID
        $folderId = '19t_HACiXuZ7DmiM6NE53jiKD88-BXX6N?dmr=1&ec=wgc-drive-globalnav-goto'; // Replace with your actual folder ID

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

private function getFolderIdByName($folderName)
{
    try {
        \Log::info("Searching for folder: " . $folderName);

        $query = "mimeType='application/vnd.google-apps.folder' and name='{$folderName}'";
        $results = $this->googleDriveService->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)'
        ]);

        $files = $results->getFiles();
        \Log::info("Found " . count($files) . " matching folders");

        if (count($files) == 0) {
            \Log::info("Creating new folder: " . $folderName);

            $folderMetadata = new Google_Service_Drive_DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $this->googleDriveService->files->create($folderMetadata, [
                'fields' => 'id'
            ]);

            \Log::info("Created folder with ID: " . $folder->getId());
            return $folder->getId();
        }

        \Log::info("Using existing folder with ID: " . $files[0]->getId());
        return $files[0]->getId();
    } catch (\Exception $e) {
        \Log::error("Folder operation failed: " . $e->getMessage());
        throw $e;
    }
}
}
