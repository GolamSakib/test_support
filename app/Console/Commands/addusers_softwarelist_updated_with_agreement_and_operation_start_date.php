<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\Customer;
use Illuminate\Console\Command;
use App\Models\CustomerSoftware;

class addusers_softwarelist_updated_with_agreement_and_operation_start_date extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addusers_softwarelist_updated_with_agreement_and_operation_start_date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'addusers_softwarelist_updated_with_agreement_and_operation_start_date';

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
    $clientSoftwareLists = CustomerSoftware::all();

    foreach ($clientSoftwareLists as $clientSoftware) {
        $clientId = $clientSoftware->client_id;
        $client = Customer::where('id', $clientId)->first();

        $agreement_date = $client->agreement_date ? Carbon::parse($client->agreement_date)->format('Y-m-d H:i:s.000') : null;
        $operation_start_date = $client->operation_starting_date ? Carbon::parse($client->operation_starting_date)->format('Y-m-d H:i:s.000') : null;

        $clientSoftware->agreement_date = $agreement_date;
        $clientSoftware->operation_start_date = $operation_start_date;
        $clientSoftware->save();
    }

    $this->info("addusers softwarelist updated with agreement and operation start date");
}
}
