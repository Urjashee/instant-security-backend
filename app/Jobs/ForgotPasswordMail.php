<?php

namespace App\Jobs;

use App\Mail\ForgotPassword;
use App\Mail\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $email;
    private $token;
    private $name;
    private $siteName;
    private $roleId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,$token,$name,$siteName,$roleId)
    {
        $this->email = $email;
        $this->token = $token;
        $this->name = $name;
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
            ->send(new ResetPassword($this->email,$this->token,$this->siteName,2,$this->roleId));
    }
}
