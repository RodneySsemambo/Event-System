<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCompleted extends Notification implements ShouldQueue
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
            ->subject("How was your event?")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your booking for **{$this->booking->event_type}** is marked completed.")
            ->line("We'd love to hear how it went — your review helps other clients find the right provider.")
            ->action('Leave a review', $link)
            ->line('Reviews are only possible on completed bookings, and they help providers build trust on Evently.')
            ->salutation('— Evently');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'      => 'booking_completed',
            'booking_id' => $this->booking->id,
            'event_type' => $this->booking->event_type,
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $link = config('app.frontend_url') . "/bookings?highlight={$this->booking->id}";

        return "Your Evently booking for {$this->booking->event_type} is complete. "
            . "Leave a review: {$link}";
    }
}
