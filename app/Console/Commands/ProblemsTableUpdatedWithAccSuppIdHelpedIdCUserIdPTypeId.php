<?php

namespace App\Console\Commands;

use App\Models\ClientUser;
use App\Models\ProblemType;
use App\Models\User;
use App\Models\Support;
use Illuminate\Console\Command;

class ProblemsTableUpdatedWithAccSuppIdHelpedIdCUserIdPTypeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'problemsTableUpdatedWithAccSuppIdHelpedIdCUserIdPTypeId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'problemsTableUpdatedWithAccSuppIdHelpedIdCUserIdPTypeId';

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
        foreach($problems as $problem){
            // updated accepted_support_id
            if(!empty($problem->accepted_support_username)){
                $acceptedSupportUser = User::where('username', $problem->accepted_support_username)
                    ->first();

                if($acceptedSupportUser){
                    $problem->update([
                        'accepted_support_id' => $acceptedSupportUser->id
                    ]);
                    $this->info('Problems table updated with accepted_support_id!');
                }else{
                    $this->warn('No accepted_support_id found with username: ' . $problem->accepted_support_username);
                }
            }



            // updated helped_by_id
            if(!empty($problem->helped_by)){
                $helpedByUser = User::where('username', $problem->helped_by)
                    ->first();
                if($helpedByUser){
                    $problem->update([
                        'helped_by_id' => $helpedByUser->id
                    ]);
                    $this->info('Problems table updated with helped_by_id!');
                }else{
                    $this->warn('No helped_by_id found  with username: '. $problem->helped_by);
                }
            }

            // updated client_user_id
            if(!empty($problem->client_user_name)){
                $clientUser = ClientUser::where('username', $problem->client_user_name)
                    ->first();
                if($clientUser){
                    $problem->update([
                        'client_user_id' => $clientUser->id
                    ]);
                    $this->info('Problems table updated with client_user_id!');
                }else{
                    $this->warn('No client_user_id found  with username: '. $problem->client_user_name);
                }
            }

            // updated problem_type_id
            if(!empty($problem->p_type)){
                $problemType = ProblemType::where('typeName', $problem->p_type)
                    ->first();
                if($problemType){
                    $problem->update([
                        'problem_type_id' => $problemType->id
                    ]);
                    $this->info('Problems table updated with problem_type_id!');
                }else{
                    $this->warn('No problem_type_id found  with typeName: '. $problem->p_type);
                }
            }

        }

        $this->info('Problems table updated done!');

    }
}
