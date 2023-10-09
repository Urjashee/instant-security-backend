<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $token;
    public $siteName;
    public $type;
    public $roleId;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$token,$siteName,$type,$roleId)
    {
        $this->email = $email;
        $this->token = $token;
        $this->siteName = $siteName;
        $this->type = $type;
        $this->roleId = $roleId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->roleId == 1) {
            return $this->subject('Here is your link to reset your Instant Security password')->markdown('emails.resetAdminPassword');
        } else {
            return $this->subject('Here is your link to reset your Instant Security password')->markdown('emails.resetPassword');
        }
    }
}
