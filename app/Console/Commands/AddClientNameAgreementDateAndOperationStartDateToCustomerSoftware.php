<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerSoftware;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddClientNameAgreementDateAndOperationStartDateToCustomerSoftware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddClientNameAgreementDateAndOperationStartDateToCustomerSoftware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AddClientNameAgreementDateAndOperationStartDateToCustomerSoftware';

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
    $customers = Customer::all();

    $progressBar = $this->output->createProgressBar($customers->count());
    $progressBar->start();

    foreach ($customers as $customer) {
        $software = CustomerSoftware::where('client_id', $customer->id)->update([
            'client_name' => $customer->cusname,
            'agreement_date' => $customer->agreement_date ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000') : null,
            'operation_start_date' => $customer->operation_starting_date ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000') : null,
        ]);

        $progressBar->advance();
    }

    $progressBar->finish();
    $this->newLine();
    $this->info("addusers_softwarelist updated with client_name, operation_start_date and agreement_date successfully");
}
}
