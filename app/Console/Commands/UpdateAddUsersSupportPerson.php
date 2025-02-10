<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Models\SoftwareSupportPerson;

class UpdateAddUsersSupportPerson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateAddUsersSupportPerson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UpdateAddUsersSupportPerson';

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
        $user_support_person = SoftwareSupportPerson::all();

        foreach ($user_support_person as $key => $value) {
            $user = User::where('username', $value->supportperson)->first();

            if ($user !== null) {
                // User was found, proceed to update support_person_id

                $value->support_person_id = $user->id;
                $value->save();
            } else {
                // Handle the case where no user was found
                $this->info("No user found for support person: {$value->supportperson}");
            }
        }

        $this->info('addusers_supportperson table updated with the support person ID from supportadmin_support_user table');

    }
}
