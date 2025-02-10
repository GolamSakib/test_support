<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerSoftware;
use App\Models\Software;

class UpdatedSoftwareListSoftwareId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateSoftwareId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Customer Software List Software id based on software Name';

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
        $customerSoftwares = CustomerSoftware::whereNull('software_id')->get();

        if ($customerSoftwares->isEmpty()) {
            $this->info("No customerSoftwares to update.");
            return;
        }

        // Batch fetch software IDs
        $softwareMap = Software::whereIn('soft_name', $customerSoftwares->pluck('software_name'))->pluck('id', 'soft_name');

        foreach ($customerSoftwares as $soft) {
            if (isset($softwareMap[$soft->software_name])) {
                if (is_null($soft->software_id)) {
                    $soft->software_id = $softwareMap[$soft->software_name];
                    $soft->save();
                    $this->info("$soft->software_name updated with software_id {$softwareMap[$soft->software_name]} successfully");
                }
            }
        }

        $this->info("Total {$customerSoftwares->count()} records processed.");
    }
}
