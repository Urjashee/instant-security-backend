<?php

namespace App\Jobs;

use App\Mail\JobInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email;
    private $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,$message)
    {
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)
            ->send(new JobInfo($this->email,$this->message));
    }
}
