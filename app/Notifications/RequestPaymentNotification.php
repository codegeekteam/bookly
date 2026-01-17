<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestPaymentNotification extends Notification implements ShouldQueue
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

    /* -------------------- ENGLISH -------------------- */

    private function getTitle()
    {
        return 'Payment request for completed service';
    }

    private function getBody()
    {
        return 'Your service has been completed. Please proceed with payment for appointment # ' 
            . $this->appointment->id;
    }

    /* -------------------- ARABIC -------------------- */

    private function getTitleAr()
    {
        return 'طلب دفع للخدمة المكتملة';
    }

    private function getBodyAr()
    {
        return 'تم إكمال الخدمة. يرجى متابعة الدفع للموعد رقم ' 
            . $this->appointment->id;
    }

    // Method to get token
    private function getToken()
    {
        return $this->appointment->customer->user->firebase_token;
    }

    /* -------------------- FIREBASE -------------------- */

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken(); // $notifiable->firebase_token;

        return (new FirebaseNotification)
            ->withTitle($this->getTitle())
            ->withBody($this->getBody())
            ->withAdditionalData([
                'redirect_id' => (string) $this->appointment->id,
                'redirect_action' => 'payment',
            ])
            ->withToken($fcm_token)
            ->sendNotification();
    }

    /* -------------------- DATABASE -------------------- */

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'title_ar' => $this->getTitleAr(),
            'body_ar' => $this->getBodyAr(),
            'redirect_id' => (string) $this->appointment->id,
            'redirect_action' => 'payment',
        ];
    }
}
