<?php

namespace App\Console\Commands;

use App\Common\FunctionHelpers\JamSessionFunction;
use App\Http\Controllers\JamSessionController;
use App\Models\JamSessionProfiles;
use App\Models\JamSessions;
use App\Models\Notifications;
use App\Models\RejectedUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RejectedUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rejected:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove users which have not accepted the request in the last 72 hours';

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
        JamSessionFunction::rejectUsers();
    }
}
