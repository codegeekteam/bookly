<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use GGInnovative\Larafirebase\Messages\FirebaseMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentCompleteNotification extends Notification implements ShouldQueue
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
        return "New appointment Complete service";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Please complete the service and mark appointment # ' . $this->appointment->id . ' as complete';
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "يرجى إكمال الخدمة";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'يرجى إكمال الخدمة وتأكيد الموعد رقم : ' . $this->appointment->id;
    }

    // Method to get token
    private function getToken()
    {
        return $this->appointment->serviceProvider->user->firebase_token;
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken(); //$notifiable->firebase_token;
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
            //'image_url' => $this->order->items->first()->product->thumbnail,
        ];
    }
}
