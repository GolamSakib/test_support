<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\ClientUser;
use Illuminate\Console\Command;

class AreaTableUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AreaTableUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AreaTableUpdate';

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
        $clientUsers = ClientUser::select('area','dist_id')->distinct()->get();
        foreach($clientUsers as $clientUser){
            if(!empty($clientUser->area)){
                Area::create([
                    'name' => $clientUser->area,
                    'district_id' => $clientUser->dist_id
                ]);
                $this->info("updated area: " . $clientUser->area . ", dist_id: " . $clientUser->dist_id);
            }
        }

    }
}
