<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClientUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;


class AssigningRoleToExistingUserInUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AssigningRoleToExistingUserInUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AssigningRoleToExistingUserInUser';

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
       $user=User::all();
       DB::table('model_has_roles')->truncate();
       foreach($user as $key => $user){
           if($user->role=='support'){
            $user->assignRole('Support');
           }
           if($user->role=='super admin'||$user->role=='admin'){
            $user->assignRole('Admin');
           }
       }
       $clientUsers=ClientUser::all();
       foreach($clientUsers as $key => $user){
            $user->assignRole('Client');
       }
       $this->info('Users with roles assigned successfully');

    }
}
