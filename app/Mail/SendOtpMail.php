<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the email message.
     */
    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->text('Your OTP code is: ' . $this->otp); // Directly set plain text
    }
}
