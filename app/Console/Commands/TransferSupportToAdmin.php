<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransferSupportToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:transfer-support-to-admin';

    protected $description = 'support request will be transferred to admin after 15 minutes';

    /**
     * The console command description.
     *
     * @var string
     */

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
        $thresholdTime = Carbon::now()->subMinutes(1);
        DB::table('supportadmin_problems')->where('is_pending', 1)->where('requested_time', '<=', $thresholdTime)->update(
            [
                'is_transfer' => 1,
                'is_pending' => 0,
            ]
        );
        $this->info('Support request is handed over to Admin');
    }
}
