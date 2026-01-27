<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetOTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;
    public $supportUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $userName, $supportUrl = null)
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->supportUrl = $supportUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.password-reset-otp')
            ->subject('Password Reset - OTP Code')
            ->with([
                'otp' => $this->otp,
                'userName' => $this->userName,
                'supportUrl' => $this->supportUrl
            ]);
    }
}
