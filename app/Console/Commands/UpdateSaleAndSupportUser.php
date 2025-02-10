<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateSaleAndSupportUser extends Command
{
    protected $signature = 'UpdateSaleAndSupportUser';
    protected $description = 'UpdateSaleAndSupportUser';

    public function handle()
    {
        try {
            // Fetch data from API
            $response = Http::get('http://software.mediasoftbd.com/msbill/api/User/GetAllActiveUser');

            if (!$response->successful()) {
                $this->error('Failed to fetch data from API');
                return 1;
            }

            $apiData = $response->json();

            // Filter users with Support or Sales roles
            $filteredUsers = collect($apiData['data'])->filter(function ($user) {
                return in_array($user['roleName'], ['Support', 'Sales']);
            });

            // Process each filtered user
            foreach ($filteredUsers as $userData) {
                // Find or create the appropriate role
                $roleName = $userData['roleName'] === 'Sales' ? 'Marketing' : 'Support';



                $user = User::whereRaw('LOWER(username) LIKE ?', ['%' . strtolower($userData['username']) . '%'])->first();

                if ($user) {
                    // Update existing user
                    $user->update([
                        'is_billing_user' => true
                    ]);
                    $this->info("Updated user: {$userData['username']}");
                } else {
                    // Create new user
                    User::create([
                        'username' => $userData['username'],
                        'full_name' => $userData['fullName'],
                        'isactive' => true,
                        'is_status_active' => true,
                        'is_billing_user' => true,
                        'role' => $roleName
                    ]);
                    $this->info("Created new user: {$userData['username']}");
                }
            }

            $this->info('User update process completed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}


