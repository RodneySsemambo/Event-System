<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreated extends Notification implements ShouldQueue
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
            ->subject("New booking request — {$this->booking->event_type}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You have a new booking request on Evently.")
            ->line("**Event:** {$this->booking->event_type}")
            ->line("**Date:** {$this->booking->event_date->format('d M Y')}")
            ->line("**Location:** {$this->booking->location}")
            ->line("**Guests:** {$this->booking->guest_count}")
            ->line("Client pays: UGX " . number_format($this->booking->quoted_amount))
            ->line("Evently fee (10%): UGX " . number_format($this->booking->commission_amount))
            ->line("Your payout: UGX " . number_format($this->booking->provider_payout))
            ->action('Review request', $link)
            ->line('Accept or decline from your bookings page. Clients can only pay after you accept.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'booking_created',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return "New Evently booking: {$this->booking->event_type} on "
            . $this->booking->event_date->format('d M Y')
            . ". Respond: {$link}";
    }
}
