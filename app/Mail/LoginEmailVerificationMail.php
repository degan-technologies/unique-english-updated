<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginEmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;
    public $userEmail;
    public $supportUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $userName, $userEmail, $supportUrl = null)
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->supportUrl = $supportUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.login-email-verification')
            ->subject('Login Email Verification - Unique English')
            ->with([
                'otp' => $this->otp,
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'supportUrl' => $this->supportUrl
            ]);
    }
}
