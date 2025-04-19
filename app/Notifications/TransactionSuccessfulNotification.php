<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionSuccessfulNotification extends Notification
{
    use Queueable;

    protected $txRef;
    protected $amount;

    public function __construct($txRef, $amount)
    {
        $this->txRef = $txRef;
        $this->amount = $amount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // You can add sms, etc.
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Payment Successful')
            ->greeting('Hello ' . $notifiable->first_name)
            ->line("Your payment of {$this->amount} ETB was successful.")
            ->line("Transaction Reference: {$this->txRef}")
            ->action('View Invoice', url("/#/invoice-page/{$this->txRef}"))
            ->line('Thank you for your purchase!');
    }

    public function toArray($notifiable)
    {
        return [
            'tx_ref' => $this->txRef,
            'amount' => $this->amount,
            'message' => 'Payment was successful. Thank you!'
        ];
    }
}
