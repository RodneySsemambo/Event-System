<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingAccepted extends Notification implements ShouldQueue
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
        $amount = (float) ($this->booking->quoted_amount
            ?? $this->booking->providerProfile->price_range_min
            ?? 0);

        return (new MailMessage)
            ->subject("Booking accepted — {$this->booking->event_type}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Good news — your booking request has been accepted.")
            ->line("**Event:** {$this->booking->event_type}")
            ->line("**Date:** {$this->booking->event_date->format('d M Y')}")
            ->line("**Location:** {$this->booking->location}")
            ->line("**Amount:** UGX " . number_format($amount))
            ->action('Complete payment', $link)
            ->line('Once your payment is confirmed, WhatsApp messaging with the provider unlocks.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'booking_accepted',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return "Your Evently booking for {$this->booking->event_type} was accepted. "
            . "Pay now: {$link}";
    }
}
