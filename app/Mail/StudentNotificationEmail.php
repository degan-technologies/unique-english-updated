<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    
    public  $studentName;
    public  $courseName;
   
    /**
     * Create a new message instance.
     */
    public function __construct($studentName, $courseName) { 
        $this->studentName = $studentName;
        $this->courseName = $courseName; 
    }

    public function build() {
        return $this->subject("Welcome to  $this->courseName – Your Learning Journey Starts Now!")
                    ->view('emails.StudentNotificationEmail')
                    ->with([ 
                       'studentName'      =>  $this->studentName, 
                       'courseName'       =>  $this->courseName,
                    ]);
    }
}
