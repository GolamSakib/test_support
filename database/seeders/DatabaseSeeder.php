<?php

// use Database\Seeds\RoleSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SoftwareTypeSeeder;
use Database\Seeders\BusinessSettingSeeder;
use Database\Seeders\PermissionSectionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *

     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(SoftwareTypeSeeder::class);
        $this->call(PermissionSectionSeeder::class);
        $this->call(BusinessSettingSeeder::class);
    }
}
