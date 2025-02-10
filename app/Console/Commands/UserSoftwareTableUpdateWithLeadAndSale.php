<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClientUser;
use Illuminate\Console\Command;
use App\Models\CustomerSoftware;


class UserSoftwareTableUpdateWithLeadAndSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UserSoftwareTableUpdateWithLeadAndSale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UserSoftwareTableUpdateWithLeadAndSale';

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
        // Fetch all clients
        $clients = CustomerSoftware::all();
        CustomerSoftware::query()->update([
            'lead_by_id'=>null
        ]);

        // Iterate over each client
        foreach ($clients as $client) {
            // Check if lead_by is not null or empty
            $lead_by_id=[];
            $lead_by_name=[];
            if ($client->lead_by !== null && $client->lead_by !== "") {
                // Find user by lead_by username
                $users=explode(',',$client->lead_by);
                foreach($users as $usr){
                    $user = User::where('username', $usr)->where('role','Marketing')->first();
                    if ($user !== null) {
                        $lead_by_id[]=$user->id;
                        $lead_by_name[]=$user->username;
                        $client->lead_by_id = json_encode($lead_by_id);
                        $client->lead_by = json_encode($lead_by_name);
                        $client->save();
                        $this->info("Updated lead_by_id for client: {$client->id} with user ID: {$user->id}");
                    } else {
                        // Log information about the missing user
                        $this->info("No user found with username: {$client->lead_by} for client: {$client->id}");
                    }
                }

            } else {
                // Handle cases where lead_by is null or empty if necessary
                $this->info("Lead by username is missing for client: {$client->id}");
            }
        }

        // Final info message
        $this->info('addusers_softwarelist table updated with lead_by_id');

        $clnts = ClientUser::select('client_id', 'sell_by_id', 'sell_by')
        ->whereNotNull('sell_by_id')
        ->groupBy('client_id', 'sell_by_id', 'sell_by')
        ->get();
            foreach ($clnts as $client) {
                $software = CustomerSoftware::where('client_id', $client->client_id)->get();
                foreach ($software as $software) {
                    $software->sell_by_id = $client->sell_by_id;
                    $software->sell_by = $client->sell_by;
                    $software->save();
                }
        }
    }

}
