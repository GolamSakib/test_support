<?php

namespace Database\Seeders;

use App\Models\PermissionSection;
use Illuminate\Database\Seeder;

class PermissionSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = [
            'day_plan',
            'quick_access',
            'catalogue',
            'role_and_permissions',
            'customer',
            'problem',
            'review',
            'push_notification',
            'complain',
            'support_request',
            'payment',
            'inventory_request',
            'license',
            'change/update_request',
            'training_schedule',
            'Catalog',
            'Support',
            'Support_Dashboard',
            'Home',
            'money receipt'
        ];

        foreach($sections as $section){
            PermissionSection::updateOrCreate([
                'name' => $section,
                'is_active' => 1
            ]);
        }
    }
}
