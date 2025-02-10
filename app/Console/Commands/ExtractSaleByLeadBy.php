<?php

namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\ClientSupportAdminSellByName;


class ExtractSaleByLeadBy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExtractSaleByLeadBy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ExtractSaleByLeadBy';

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

        $saleByLeadByPersons=ClientSupportAdminSellByName::all();
        foreach($saleByLeadByPersons as $saleByLeadByPerson){
            $users=explode(",",$saleByLeadByPerson->sell_by_name);
            foreach($users as $usr){
                $user=User::UpdateOrCreate([
                    'username'=>$usr,
                    'role'=>'Marketing',
                ]
                ,[
                    'username'=>$usr,
                    'password'=>'123456',
                    'role'=>'Marketing',
                    'is_status_active'=>true,
                ]);
                $user->assignRole('Marketing');
                $token = $user->createToken('API Token')->plainTextToken;
            }
        }
        $this->info('Sale By Lead By Extracted Successfully to supportadmin_support_user table');

    }
}
