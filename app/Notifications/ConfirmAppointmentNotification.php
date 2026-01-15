<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmAppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
        $this->onQueue('default');
    }

    public function via($notifiable): array
    {
        return ['database', 'firebase'];
    }

    // Method to set the title dynamically
    private function getTitle()
    {
        return 'appointment confirmed';
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'your appointment # '.$this->appointment->id.' confirmed';
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return 'تم قبول الموعد';
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'تم قبول موعد رقم :  '.$this->appointment->id;
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $notifiable->firebase_token;

        return (new FirebaseNotification)
            ->withTitle($this->getTitle())
            ->withBody($this->getBody())
            ->withAdditionalData([
                'redirect_id' => (string) $this->appointment->id,
                'redirect_action' => 'appointments',
            ])
            ->withToken($fcm_token)
            ->sendNotification();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'title_ar' => $this->getTitleAr(),
            'body_ar' => $this->getBodyAr(),
            'redirect_id' => (string) $this->appointment->id,
            'redirect_action' => 'appointments',
            // 'image_url' => $this->order->items->first()->product->thumbnail,
        ];

    }
}
