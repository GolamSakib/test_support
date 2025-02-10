<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Inventory;
use App\Models\ClientUser;
use Illuminate\Console\Command;
use App\Models\CustomerSoftware;


class InventoryTableUpdateWithClientUserAndAssignedPersonId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'InventoryTableUpdateWithClientUserAndAssignedPersonId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'InventoryTableUpdateWithClientUserAndAssignedPersonId';

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
       $inventories=Inventory::all();
       foreach ($inventories as $inventory) {
           $client_user_info=ClientUser::where('username',$inventory->client_user_name)->first();
        if($client_user_info){
            $inventory->client_user_id=$client_user_info->id;
            $inventory->save();
        }
        else{
            $this->warn("No Client User found for: {$inventory->id}");
        }
       }

       $inventories_that_has_been_assigned=Inventory::where('is_assigned',1)->get();
       foreach ($inventories_that_has_been_assigned as $inventory) {
        $assigned_person=User::where('username',$inventory->assigned_person)->first();
        if($assigned_person){
            $inventory->assigned_person=$assigned_person->username;
            $inventory->assigned_person_id=$assigned_person->id;
            $inventory->save();
        }
        else{
            $this->warn("No Assigned Person found for: {$inventory->id}");
        }

       }
       $this->info('Inventory table has been successfully updated.');
       return Command::SUCCESS;
    }


}
