<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessful extends Notification implements ShouldQueue
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
            ->subject("Payment confirmed — {$this->booking->event_type}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your payment has been confirmed.")
            ->line("**Event:** {$this->booking->event_type}")
            ->line("**Date:** {$this->booking->event_date->format('d M Y')}")
            ->line("**Amount paid:** UGX " . number_format($amount))
            ->action('View booking', $link)
            ->line('Your booking is now confirmed and your date is locked in.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'payment_successful',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";
        $amount = (float) ($this->booking->quoted_amount
            ?? $this->booking->providerProfile->price_range_min
            ?? 0);

        return "Payment of UGX " . number_format($amount)
            . " confirmed for your {$this->booking->event_type} booking. View: {$link}";
    }
}
