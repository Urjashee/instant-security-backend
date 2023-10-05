<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $tokens;
    public $siteName;
    public $type;
    public $firstName;
    public $roleId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$tokens,$siteName,$type,$firstName,$roleId)
    {
        $this->email = $email;
        $this->tokens = $tokens;
        $this->siteName = $siteName;
        $this->type = $type;
        $this->firstName = $firstName;
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
            return $this->subject('Here is your link to verify your Instant Security account')->markdown('emails.verifyAdmin');
        } else {
            return $this->subject('Here is your link to verify your Instant Security account')->markdown('emails.verify');
        }
    }
}
