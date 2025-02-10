<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class addusers_users_table_updated_with_password extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addusers_users_table_updated_with_password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'addusers_users_table_updated_with_password';

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
        $clientUsers = DB::table('addusers_users')->get();
        $defaultPassword = '123456';

        foreach ($clientUsers as $clientUser) {
            DB::table('addusers_users')
                ->where('id', $clientUser->id)
                ->update(['password' => Hash::make($defaultPassword)]);
        }

        $this->info("Password updated for all users.");


    }
}
