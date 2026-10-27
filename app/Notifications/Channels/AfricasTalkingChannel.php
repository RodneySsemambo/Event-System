<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class AfricasTalkingChannel
{
    public function __construct(private SmsService $sms) {}

    /**
     * Called automatically by Laravel for any Notification class that
     * lists 'africastalking' in its via() array. Expects the
     * notification to implement toAfricasTalking($notifiable): string.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toAfricasTalking')) {

            return;
        }
        $phone = $notifiable->routeNotificationFor('africastalking', $notification);
        if ($phone) {
            $this->sms->send($phone, $notification->toAfricasTalking($notifiable));
        }
    }
}
