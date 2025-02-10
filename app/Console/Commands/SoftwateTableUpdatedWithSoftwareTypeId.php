<?php

namespace App\Console\Commands;
use App\Models\Software;
use App\Models\SoftwareType;
use Illuminate\Console\Command;


class SoftwateTableUpdatedWithSoftwareTypeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SoftwateTableUpdatedWithSoftwareTypeId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SoftwateTableUpdatedWithSoftwareTypeId';

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
      $softwares=Software::all();
      foreach($softwares as $software){
       $softwareType=SoftwareType::where('software_type',$software->soft_type)->first();
       if($softwareType){
         $software->software_type_id=$softwareType->id;
         $software->save();
         $this->info("Software Table is Updated With Software Type Id: {$software->id}");
       }
      }

    }
}
