<?php
namespace App\Console\Commands;


use Illuminate\Console\Command;

class GenerateSupportAdminUserCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenerateSupportAdminUserCSV';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GenerateSupportAdminUserCSV';

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
        // Retrieve all users


    }

}
