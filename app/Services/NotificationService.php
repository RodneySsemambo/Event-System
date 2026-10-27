<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingAccepted;
use App\Notifications\BookingCompleted;
use App\Notifications\BookingCreated;
use App\Notifications\BookingDeclined;
use App\Notifications\PaymentFailed;
use App\Notifications\PaymentSuccessful;

/**
 * Notification service holds the logic for notification that is sent t the user after any booking is made 
 * it extends the files in the notifications folder by using the notify function to proccess the notification
 * through email,africastalking and database
 */
class NotificationService
{
    public function bookingCreated(User $provider, Booking $booking)
    {
        $provider->notify(new BookingCreated($booking));
    }
    public function bookingAccepted(User $client, Booking $booking)
    {
        $client->notify(new BookingAccepted($booking));
    }
    public function bookingDeclined(User $client, Booking $booking)
    {
        $client->notify(new BookingDeclined($booking));
    }
    public function paymentFailed(User $client, Booking $booking)
    {
        $client->notify(new PaymentFailed($booking));
    }
    public function paymentSuccessful(User $user, Booking $booking)
    {
        $user->notify(new PaymentSuccessful($booking));
    }
    public function bookingCompleted(User $client, Booking $booking)
    {
        $client->notify(new BookingCompleted($booking));
    }
}
