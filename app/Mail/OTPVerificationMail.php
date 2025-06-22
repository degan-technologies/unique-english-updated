<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $name;
    public $url;
    public $expiryMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $name, $url, $expiryMinutes = 5)
    {
        $this->otp = $otp;
        $this->name = $name;
        $this->url = $url;
        $this->expiryMinutes = $expiryMinutes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OTP Verification Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp-verification',
            with: [
                'otp' => $this->otp,
                'name' => $this->name,
                'url' => $this->url,
                'expiryMinutes' => $this->expiryMinutes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
