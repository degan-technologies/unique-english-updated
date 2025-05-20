<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstructorNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public    $instructorName;
    public  $studentName;
    public  $systemAdminEmail;
    public  $studentEmail;
    public  $enrollmentDate;
    public   $courseName;
    /**
     * Create a new message instance.
     */
    public function __construct($instructorName, $studentName, $systemAdminEmail, $studentEmail, $enrollmentDate, $courseName ) {
        $this->instructorName   = $instructorName;
        $this->studentName      = $studentName;
        $this->systemAdminEmail = $systemAdminEmail;
        $this->studentEmail     = $studentEmail;
        $this->enrollmentDate   = $enrollmentDate;
        $this->courseName       = $courseName;
    }
 

    public function build() {
        return $this->subject(" New Course Enrollment! $this->studentName Just Joined  $this->courseName")
                    ->view('emails.InstructorNotificationEmail')
                    ->with([
                       'instructorName'   =>  $this->instructorName,
                       'studentName'      =>  $this->studentName,
                       'systemAdminEmail' =>  $this->systemAdminEmail,
                       'studentEmail'     =>  $this->studentEmail,
                       'enrollmentDate'   =>  $this->enrollmentDate,
                       'courseName'       =>  $this->courseName,
                    ]);
    }
 
}
