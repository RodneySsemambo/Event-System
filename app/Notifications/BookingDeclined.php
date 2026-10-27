<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingDeclined extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Booking $booking) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', AfricasTalkingChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $link = config('app.frontend_url') . '/search';

        return (new MailMessage)
            ->subject("Booking declined — {$this->booking->event_type}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Unfortunately, your booking request was declined.")
            ->line("**Event:** {$this->booking->event_type}")
            ->line("**Date:** {$this->booking->event_date->format('d M Y')}")
            ->action('Browse other providers', $link)
            ->line('There are other verified providers on Evently who may be available for your date.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'booking_declined',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return "Your Evently booking for {$this->booking->event_type} was declined. "
            . "Find other providers: {$link}";
    }
}
