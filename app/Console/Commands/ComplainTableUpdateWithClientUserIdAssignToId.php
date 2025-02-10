<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Complain;
use App\Models\ClientUser;
use Illuminate\Console\Command;

class ComplainTableUpdateWithClientUserIdAssignToId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ComplainTableUpdateWithClientUserIdAssignToId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ComplainTableUpdateWithClientUserIdAssignToId';

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
        $complains=Complain::all();
        foreach ($complains as $complain) {
            $client_user_info = ClientUser::where('username',$complain->client_user_name)->first();
            if($client_user_info){
                $complain->client_user_id = $client_user_info->id;
                $complain->save();
            }
            else{
                $this->warn("No Client User found for: {$complain->id}");
            }
        }

       $complains_that_has_been_assigned = Complain::where('is_forward',1)->get();
       foreach ($complains_that_has_been_assigned as $complain) {
            $assigned_person = User::where('username', $complain->assign_to)->first();
            if($assigned_person){
                $complain->assign_to = $assigned_person->username;
                $complain->assign_to_id = $assigned_person->id;
                $complain->save();
            }
            else{
                $this->warn("No Assigned Person found for: {$complain->id}");
            }

       }
       $this->info('Complain table has been successfully updated.');
       return Command::SUCCESS;
    }
}
