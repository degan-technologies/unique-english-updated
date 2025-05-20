<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;
 
    public  $studentName; 
    public  $studentEmail;
    public  $enrollmentDate;
    public   $courseName;
    public $amount;
    /**
     * Create a new message instance.
     */
    public function __construct( $studentName, $studentEmail, $enrollmentDate, $courseName, $amount) { 
        $this->studentName = $studentName;
        $this->studentEmail     = $studentEmail;
        $this->enrollmentDate   = $enrollmentDate;
        $this->courseName       = $courseName;
        $this->amount           = $amount;
    }
 

    public function build() {
        return $this->subject("New Course Purchase")
                    ->view('emails.AdminNotificationEmail')
                    ->with([ 
                       'studentName' =>  $this->studentName,
                       'studentEmail'     =>  $this->studentEmail,
                       'enrollmentDate'   =>  $this->enrollmentDate,
                       'courseName'       =>  $this->courseName,
                       'amount'           =>  $this->amount,
                    ]);
    }
}
