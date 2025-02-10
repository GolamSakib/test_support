<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

     public function run()
{
    $permissions = [
        'day_plan' => [
            'Plans','Calender','Day Plan','Day Plan Dashboard'
        ],
        'catalogue' => [
            'Alert','Location','Problems','Software','Catalouge'
        ],
        'quick_access' => [
            'By Sales Person','By Date Range','By Support Executive','By Location','Aggrement Date','Quick Access','Filter Login User'
        ],
        'role_and_permissions' => [
            'Roles','Permissions','Role & Permission','All User'
        ],
        'support_request' => [
            'Support Request'
        ],
        'complain' => [
            'Complain'
        ],
        'payment' => [
            'Payment'
        ],
        'inventory_request' => [
            'Inventory Request'
        ],
        'training_schedule' => [
            'Training Schedule'
        ],
        'change/update_request' => [
            'Change Update Request'
        ],
        'license' => [
            'License'
        ],
        'Support' => [
            'Support','Support Inquiry','Support Executive','Support By Executive'
        ],
        'customer' => [
            'Customer'
        ],
        'Support_Dashboard' => [
            'Support Generate', 'Clients Monitoring','Inventory Alert','Training Alert','Payment Alert','Complain Alert','Collection Request','Search Support'
        ],
        'money receipt' => [
            'money receipt'
        ],
        'Home' => [
            'Home'
        ],
        'review' => [
            'Review Manager'
        ]
    ];

    foreach($permissions as $permissionSection => $permissionList){
        foreach($permissionList as $singlePermission){
            Permission::firstOrCreate(
                [
                    'name' => $singlePermission,
                    'guard_name' => 'web'
                ],
                [
                    'section' => $permissionSection
                ]
            );
        }
    }
}

}
