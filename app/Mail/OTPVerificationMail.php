<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $userName, $verificationUrl = null)
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.otp-verification')
            ->subject('Email Verification - OTP Code')
            ->with([
                'otp' => $this->otp,
                'userName' => $this->userName,
                'verificationUrl' => $this->verificationUrl
            ]);
    }
}
