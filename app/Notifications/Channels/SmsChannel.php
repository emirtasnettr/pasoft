<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Stub channel — bağlantı için Netgsm / İletimerkezi vb. entegre edin.
 */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $payload = $notification->toSms($notifiable);
        Log::info('SMS stub', ['to' => $notifiable->phone ?? null, 'payload' => $payload]);
    }
}
