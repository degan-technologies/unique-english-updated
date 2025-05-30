<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable {

    use Queueable, SerializesModels;
    public $otp;
    public $name;
    public $url;

    public function __construct($otp, $name, $url)
    {
        $this->otp  = $otp;
        $this->name = $name;
        $this->url  = $url;
    }

    public function build()
    {
        return $this->subject('OTP Verification')
                    ->view('emails.otp_verification')
                    ->with([
                        'otp'  => $this->otp,
                        'name' => $this->name,
                        'url'  => $this->url,  
                    ]);
    }
}
