<?php

namespace App\Console\Commands;

use App\Http\Controllers\SecurityJobController;
use Illuminate\Console\Command;

class AutoClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clock_out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to auto clock out';

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
        (new SecurityJobController())->autoClockOut();
    }
}
