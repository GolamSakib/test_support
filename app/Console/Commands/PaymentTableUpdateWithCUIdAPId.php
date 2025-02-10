<?php

namespace App\Console\Commands;

use App\Models\ClientUser;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;

class PaymentTableUpdateWithCUIdAPId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymentTableUpdateWithCUIdAPId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'paymentTableUpdateWithCUIdAPId';

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
        $payments = Payment::all();


        foreach ($payments as $payment) {
            if (!empty($payment->client_user_name)) {
                $clientUser = ClientUser::where('username', $payment->client_user_name)->first();

                if ($clientUser) {
                    $payment->update([
                        'client_user_id' => $clientUser->id
                    ]);
                    $this->info('Payment table updated with client_user_id: ' . $clientUser->id . ' for client: ' . $clientUser->username);
                } else {
                    $this->warn('No client user found with username: ' . $payment->client_user_name);
                }
            } else {
                $this->warn('Client user is not set for payment ID: ' . $payment->id);
            }

            if (!empty($payment->assigned_person)) {
                $supportUser = User::where('username', $payment->assigned_person)->first();

                if ($supportUser) {
                    $payment->update([
                        'assigned_person_id' => $supportUser->id
                    ]);
                    $this->info('Payment table updated with assigned_person_id: ' . $supportUser->id . ' for user: ' . $supportUser->username);
                } else {
                    $this->warn('No user found with username: ' . $payment->assigned_person);
                }
            } else {
                $this->warn('Assigned person is not set for payment ID: ' . $payment->id);
            }
        }

     

        $this->info('Payment table updated with client_user_id & assigned_person_id successfully');


    }
}
