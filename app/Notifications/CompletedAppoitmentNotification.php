<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\FirebaseNotification;
use GGInnovative\Larafirebase\Messages\FirebaseMessage;

class CompletedAppoitmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    
    public $appointment;

     /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
        $this->onQueue('default');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'firebase'];
    }

       // Method to set the title dynamically
    private function getTitle()
    {
        return "Appointment Completed service";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'completed the service for appointment # ' . $this->appointment->id ;
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
