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
    public $roleId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$tokens,$siteName,$type,$roleId)
    {
        $this->email = $email;
        $this->tokens = $tokens;
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
            return $this->subject('Here is your link to verify your Jam Session account')->markdown('emails.verifyAdmin');
        } else {
            return $this->subject('Here is your link to verify your Jam Session account')->markdown('emails.verify');
        }
    }
}
