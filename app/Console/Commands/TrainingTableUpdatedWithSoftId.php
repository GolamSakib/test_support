<?php

namespace App\Console\Commands;

use App\Models\ClientUser;
use App\Models\Software;
use App\Models\Training;
use App\Models\User;
use Illuminate\Console\Command;

class TrainingTableUpdatedWithSoftId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trainingTableUpdatedWithSoftId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'trainingTableUpdatedWithSoftId';

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
        $trainings = Training::all();

        foreach($trainings as $training){
            $software = Software::whereRaw('LOWER(soft_name) = ?', [strtolower($training->soft_name)])->first();
            $clientUser = ClientUser::whereRaw('LOWER(username) = ?', [strtolower($training->client_user_name)])->first();

            if($software){
                $training->soft_id = $software->id;
                $training->save();
                $this->info('Training table updated with soft_id successfully');
            }

            if($clientUser){
                $training->client_user_id = $clientUser->id;
                $training->save();
                $this->info('Training table updated with client_user_id  successfully');
            }

            if (!empty($training->assigned_person)) {
                $supportUser = User::where('username', $training->assigned_person)->first();

                if ($supportUser) {
                    $training->update([
                        'assigned_person_id' => $supportUser->id
                    ]);
                    $this->info('training table updated with assigned_person_id: ' . $supportUser->id . ' for user: ' . $supportUser->username);
                } else {
                    $this->warn('No user found with username: ' . $training->assigned_person);
                }
            } else {
                $this->warn('Assigned person is not set for payment ID: ' . $training->id);
            }
        }


    }
}
