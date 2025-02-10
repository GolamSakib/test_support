<?php

namespace App\Console\Commands;

use App\Models\Area;
use DB;
use Illuminate\Console\Command;

class addusers_users_table_updated_with_area_id extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addusers_users_table_updated_with_area_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'addusers_users_table_updated_with_area_id';

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
        $clientUsers = DB::table('addusers_users')
            ->select('id', 'area')
            ->get();

        foreach ($clientUsers as $clientUser) {
            // Check if the area is not empty
            if (!empty($clientUser->area)) {
                // Find the area by name
                $area = Area::where('name', $clientUser->area)->first();

                // Only update if the area exists
                if ($area) {
                    DB::table('addusers_users')
                        ->where('id', $clientUser->id)
                        ->update([
                            'area_id' => $area->id
                        ]);
                } else {
                    // Optional: Log or handle cases where the area is not found
                    $this->info("Area '{$clientUser->area}' not found for user ID: {$clientUser->id}");
                }
            }
        }

        // Final confirmation message (outside the loop)
        $this->info("addusers_users updated based on area name.");
    }
}
