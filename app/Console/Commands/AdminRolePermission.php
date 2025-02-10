<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class AdminRolePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AdminRolePermission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AdminRolePermission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissions =  Permission::get();
        $admin = Role::where('name', 'Admin')->first();
        foreach($permissions as $permission){
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permission->id,
                'role_id' => $admin->id
            ]);
        }

        $this->info("Admin all permissions added");

    }
}
