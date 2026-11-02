<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private User $user) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', AfricasTalkingChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontend = config('app.frontend_url', 'http://localhost:5173');
        $isProvider = $this->user->role === 'provider';

        $message = (new MailMessage)
            ->subject("Welcome to Evently, {$notifiable->name}!")
            ->greeting("Hi {$notifiable->name},");

        if ($isProvider) {
            $message
                ->line("Welcome to Evently — we're really glad you're here.")
                ->line("Your provider account is ready. Here's what to do next:")
                ->line("**1. Complete your profile** — add a bio, pricing, and your WhatsApp number so clients know exactly what you offer.")
                ->line("**2. Upload your portfolio** — real photos of past work are the #1 thing clients look for before they book.")
                ->line("**3. Get verified** — once verified, your profile appears in search and clients can book you directly.")
                ->action('Finish your profile', $frontend . '/profile/edit')
                ->line("Once your profile is set up, we'll start sending you booking requests from clients across Uganda.")
                ->line("If you need anything, reply to this email — a real person will read it.")
                ->salutation('Welcome aboard, — The Evently Team');
        } else {
            $message
                ->line("Welcome to Evently — we're really glad you're here.")
                ->line("You now have access to Uganda's growing network of verified event providers — caterers, decorators, photographers, DJs, MCs, and venues. Every profile you see is real and reviewed.")
                ->line("Here's what to do next:")
                ->line("**1. Browse providers** — filter by category, location, and price to find the right fit.")
                ->line("**2. Compare portfolios** — see real past work before you reach out.")
                ->line("**3. Book with confidence** — request a booking, pay through mobile money, and chat directly once confirmed.")
                ->action('Start exploring', $frontend . '/search')
                ->line("Planning your next event has never been this simple.")
                ->line("If you need anything, reply to this email — a real person will read it.")
                ->salutation('Welcome aboard, — The Evently Team');
        }

        return $message;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'event'   => 'welcome',
            'message' => $this->user->role === 'provider'
                ? 'Welcome to Evently! Complete your profile to start receiving bookings.'
                : 'Welcome to Evently! Start exploring verified providers near you.',
        ];
    }

    public function toAfricasTalking($notifiable)
    {
        $first  = explode(' ', $notifiable->name)[0];
        $prefix = $this->user->role === 'provider' ? 'Welcome to Evently' : 'Welcome to Evently';

        return "Hi {$first}, welcome to Evently! "
            . ($this->user->role === 'provider'
                ? 'Complete your profile to start receiving booking requests from clients.'
                : 'Browse and book verified event providers for any event  near you.')
            . ' Log in at ' . config('app.frontend_url');
    }
}
