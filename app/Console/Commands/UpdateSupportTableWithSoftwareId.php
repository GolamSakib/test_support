<?php

namespace App\Console\Commands;
use App\Models\Support;
use App\Models\Software;
use Illuminate\Console\Command;

class UpdateSupportTableWithSoftwareId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateSupportTableWithSoftwareId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UpdateSupportTableWithSoftwareId';

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
        $problems = Support::all();

        foreach ($problems as $problem) {
            // Find the first matching software by name
            $software = Software::where('soft_name', 'like', '%' . $problem->soft_name . '%')->first();

            // Check if software is found
            if ($software) {
                $problem->soft_id = $software->id;
                $problem->save();
            } else {
                // Handle cases where no matching software is found
                $this->warn("No matching software found for problem ID: {$problem->id} with software name: {$problem->soft_name}");
            }
        }

        $this->info("supportadmin_problems table updated with software_id where applicable.");
    }

}
