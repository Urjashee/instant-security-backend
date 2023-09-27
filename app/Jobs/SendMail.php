<?php

namespace App\Jobs;

use App\Mail\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $email;
    private $token;
    private $siteName;
    private $roleId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,$token,$siteName,$roleId)
    {
        $this->email = $email;
        $this->token = $token;
        $this->siteName = $siteName;
        $this->roleId = $roleId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)
            ->send(new VerifyEmail($this->email,$this->token,$this->siteName,1,$this->roleId));
    }
}
