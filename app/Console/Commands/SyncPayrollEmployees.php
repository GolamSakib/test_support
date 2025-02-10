<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncPayrollEmployees extends Command
{
    protected $signature = 'app:sync-payroll-employees';
    protected $description = 'Sync employees from payroll API with users';

    public function handle()
    {
        try {
            // Make POST request to the payroll API
            $response = Http::asForm()
                ->post('http://payroll.mediasoftbd.com/DashBoardWebService.asmx/GetEmployeeListByBranch', [
                    'branchId' => '10001'
                ]);

            if (!$response->successful()) {
                $this->error('Failed to fetch data from payroll API');
                return 1;
            }

            $responseData = $response->json();

            if (!isset($responseData['Data']) || !$responseData['ExecutionState']) {
                $this->error('Invalid or empty response from API');
                return 1;
            }

            foreach ($responseData['Data'] as $employee) {
                // Convert employee name to lowercase for better matching
                $employeeName = Str::lower($employee['EMP_NAME']);

                // Try to find user by matching username with employee name
                $user = User::where(function($query) use ($employeeName) {
                    // Try different name matching strategies
                    $query->whereRaw('LOWER(username) LIKE ?', ["%{$employeeName}%"])
                          ->orWhereRaw('LOWER(username) LIKE ?', ["%".str_replace(' ', '', $employeeName)."%"]);
                })->first();

                if ($user) {
                    // Update existing user
                    $user->update([
                        'is_payroll_user' => true,
                        'payroll_id' => $employee['$id']
                    ]);

                    $this->info("Updated user: {$user->username} with payroll data");
                } else {
                    // Create new user
                    $newUser = User::create([
                        'full_name' => $employee['EMP_NAME'],
                        'email' => $employee['EMAIL'] ?: null,
                        'phone_no' => $employee['PHONE_MOBILE'] ?: null,
                        'is_payroll_user' => true,
                        'payroll_id' => $employee['$id'],
                        'username' => Str::slug($employee['EMP_NAME']), // Create username from name
                        'is_active' => true,
                        'is_status_active' => true,
                    ]);

                    $this->info("Created new user: {$newUser->username}");
                }
            }

            $this->info('Payroll employee sync completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
