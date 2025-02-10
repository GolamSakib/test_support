<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerSoftware;
use Illuminate\Console\Command;

class AddClientNameAndDateToCustomerSoftware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddClientNameAndDateToCustomerSoftware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AddClientNameAndDateToCustomerSoftware';

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
      $software=CustomerSoftware::all();
      foreach($software as $soft){
       $customer_info=Customer::where('id',$soft->client_id)->first();
       $soft->client_name=$customer_info->cusname;
       $soft->operation_start_date=$customer_info->operation_starting_date;
       $soft->agreement_date=$customer_info->agreement_date;
       $soft->save();
      }
      $this->info("addusers_softwarelist updated with client_name,operation_start_date and agreement_date successfully");
    }
}

