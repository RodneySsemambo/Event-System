<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Booking $booking) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', AfricasTalkingChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return (new MailMessage)
            ->subject("Payment not completed — {$this->booking->event_type}")
            ->greeting("Hi {$notifiable->name},")
            ->line("We couldn't complete your payment for this booking.")
            ->line("**Event:** {$this->booking->event_type}")
            ->line("**Date:** {$this->booking->event_date->format('d M Y')}")
            ->line('This usually means the mobile money wallet doesn\'t have enough balance, or the prompt wasn\'t approved in time.')
            ->action('Try payment again', $link)
            ->line('Your booking is still active — you can retry as soon as your wallet is topped up.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'payment_failed',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return "Payment for your {$this->booking->event_type} booking did not go through. "
            . "Try again: {$link}";
    }
}
