<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class AddusersCustomerTableUpdatedWithIsRegistered extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddusersCustomerTableUpdatedWithIsRegistered';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AddusersCustomerTableUpdatedWithIsRegistered';

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
        $customers = DB::table('addusers_customer')->get();

        foreach ($customers as $customer) {
            if ($customer->is_active == true) {
                DB::table('addusers_customer')
                    ->where('id', $customer->id) // Assuming `id` is the primary key
                    ->update(['is_registered' => true]);

                $this->info("is_registered updated for: " . $customer->id);
            } else {
                $this->warn("is_registered not updated for: " . $customer->id);
            }
        }
    }
}
