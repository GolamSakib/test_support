<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SupportAdminUserSyncWithPayroll extends Command
{
    protected $signature = 'SupportAdminUserSyncWithPayroll';
    protected $description = 'SupportAdminUserSyncWithPayroll';

    public $payrollUsers = [];
    public $existingUsers = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->payrollUsers = $this->getPayrollUsers();
        $this->existingUsers = $this->getExistingUsersFromExcel();
        $this->updateUserWithPayrollData();
    }

    public function getPayrollUsers()
    {
        $response = Http::get('http://payroll.mediasoftbd.com/DashBoardWebService.asmx/GetLoginUserList');
        if ($response->successful()) {
            $data = $response->json();
            if ($data['ExecutionState'] === true) {
                return $data['Data'];
            }
        }
        $this->error('Failed to fetch payroll users');
        return [];
    }

    public function getExistingUsersFromExcel()
    {
        $path = storage_path('excel/support_user.csv');
        $users = [];

        if (($handle = fopen($path, "r")) !== FALSE) {
            // Get headers from first row
            $headers = array_map(function($header) {
                return strtolower(trim($header)); // Convert headers to lowercase and trim spaces
            }, fgetcsv($handle));

            // Read remaining rows
            while (($data = fgetcsv($handle)) !== FALSE) {
                // Combine headers with data to create object
                $user = new \stdClass();
                foreach ($headers as $index => $header) {
                    $user->$header = $data[$index] ?? null;  // Use null coalescing operator for safety
                }
                $users[] = $user;
            }
            fclose($handle);
        }

        return $users;
    }

public function updateUserWithPayrollData()
{
    $this->info('Starting user update process...');

    foreach ($this->existingUsers as $existingUser) {
        if ($existingUser->payroll_user_name !== "") {
            // Find matching payroll user
            $payrollUser = collect($this->payrollUsers)->first(function($user) use ($existingUser) {
                return $user['UserName'] === $existingUser->payroll_user_name;
            });

            if ($payrollUser) {
                // Update user in database
                $data=User::where('id', $existingUser->id)
                    ->update([
                        'username' => $payrollUser['UserName'],
                        'email' => $payrollUser['Email'],
                        'phone_no' => $payrollUser['Phone'],
                        'address' => $payrollUser['Address'],
                        'designation' => $payrollUser['Designation'],
                        'payroll_id' => $payrollUser['EID'],
                        'isactive' => $payrollUser['IsActive']
                    ]);

                $this->info("Updated user: {$payrollUser['UserName']}");
            } else {
                $this->warn("No matching payroll user found for: {$existingUser->payroll_user_name}");
            }
        }
    }

    $this->info('User update process completed.');
}


}
