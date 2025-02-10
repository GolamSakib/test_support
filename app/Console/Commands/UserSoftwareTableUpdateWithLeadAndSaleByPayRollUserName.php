<?php

namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\CustomerSoftware;


class UserSoftwareTableUpdateWithLeadAndSaleByPayRollUserName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSoftwareTableUpdateWithLeadAndSaleByPayRollUserName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UserSoftwareTableUpdateWithLeadAndSaleByPayRollUserName';

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
    $this->info('UserSoftwareTableUpdateWithLeadAndSaleByPayRollUserName started');

    $customerSoftwares = CustomerSoftware::where('sell_by_id','<>',null)->get();
    // $customerSoftwares = CustomerSoftware::where('id',2115)->get();
    $this->info("Found {$customerSoftwares->count()} records to process");

    foreach($customerSoftwares as $customerSoftware) {
        try {
            $sell_by_id = json_decode($customerSoftware->sell_by_id);
            $lead_by_id = json_decode($customerSoftware->lead_by_id);

            $sell_by_name = [];
            $lead_by_name = [];

            foreach($sell_by_id as $sell_by_id_value) {
                $user = User::where('id', $sell_by_id_value)->first();
                if($user) {
                    $sell_by_name[] = $user->username;
                }
            }

            foreach($lead_by_id as $lead_by_id_value) {
                $user = User::where('id', $lead_by_id_value)->first();
                if($user) {
                    $lead_by_name[] = $user->username;
                }
            }

            $customerSoftware->sell_by = json_encode($sell_by_name);
            $customerSoftware->lead_by = json_encode($lead_by_name);
            $customerSoftware->save();

            $this->info("Updated CustomerSoftware ID: {$customerSoftware->id}");
        } catch(\Exception $e) {
            $this->error("Error processing CustomerSoftware ID: {$customerSoftware->id}");
            $this->error($e->getMessage());
        }
    }

    $this->info('UserSoftwareTableUpdateWithLeadAndSaleByPayRollUserName completed');
}
}
