<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\V2\SyncController;

class CustomerSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Customer:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customer Sync';

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
    public function handle(SyncController $sync)
    {
        $sync->syncSoftware();
        $this->info("customer sync successfuly");
    }
}
