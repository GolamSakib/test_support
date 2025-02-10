<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\User;
use App\Models\ClientUser;
use Illuminate\Console\Command;
use App\Models\CustomerSoftware;


class usersTableUpdateWithSellByIdAndLeadByIdAndLeadBy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usersTableUpdateWithSellByIdAndLeadByIdAndLeadBy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'usersTableUpdateWithSellByIdAndLeadByIdAndLeadBy';

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
        // Retrieve all clients from ClientUser model
        $clients = ClientUser::all();

        // Clear existing data
        ClientUser::query()->update([
            'sell_by_id' => null,
            'lead_by_id' => null,
            'lead_by' => null,
        ]);

        foreach ($clients as $client) {
            $sell_by_id = [];
            $sell_by_name = [];

            // Split the sell_by string into an array
            $sale_by = explode(',', $client->sell_by);

            // Fetch all relevant users in one query
            $users = User::whereIn('username', $sale_by)
                ->where('role', 'Marketing')
                ->get(['id', 'username']);

            foreach ($users as $user) {
                // Collect the IDs and usernames
                $sell_by_id[] = $user->id;
                $sell_by_name[] = $user->username;
            }

            // Save the client data only once
            if (!empty($sell_by_id)) {
                $client->sell_by_id = json_encode($sell_by_id);
                $client->sell_by = json_encode($sell_by_name);
                
                $client->save();
            } else {
                // Output information if no sales person is found
                $this->info("No sales man for the client: {$client->username}");
            }


            if(!empty($client->area)){
                $area = Area::where('name', $client->area)->first();
                if($area){
                    $client->area_id = $area->id;
                    $client->save();
                }
            }
        }

        $this->info('addusers_users table updated with sell by id');


        $leadByPersonNameAndClient = CustomerSoftware::select('client_id', 'lead_by')
        ->groupBy('client_id', 'lead_by')
        ->get();

        foreach ($leadByPersonNameAndClient as $val) {
            if ($val->lead_by !== null && $val->lead_by !== "") {
                $lead_by = explode(',', $val->lead_by);
                $leadById = [];
                $leadByName = [];

                // Get all relevant users in one query
                $users = User::whereIn('username', $lead_by)
                    ->where('role', 'Marketing')
                    ->get(['id', 'username']);

                foreach ($users as $user) {
                    $leadById[] = $user->id;
                    $leadByName[] = $user->username;
                }

                $clientUsers = ClientUser::where('client_id', $val->client_id)->get();
                foreach ($clientUsers as $user) {
                    if (!empty($leadById)) {
                        $user->lead_by_id = json_encode($leadById);  // Encode the full array
                        $user->lead_by = json_encode($leadByName);   // Encode the full array
                        $user->client_id = $val->client_id;
                        $user->save();
                    } else {
                        $this->info("No marketing lead found for lead_by: {$val->lead_by}");
                    }
                }
            }
        }

    }


}
