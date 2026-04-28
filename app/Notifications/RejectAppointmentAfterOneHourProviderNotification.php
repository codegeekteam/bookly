<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class RejectAppointmentAfterOneHourProviderNotification extends Notification implements ShouldQueue
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
    public function via(object $notifiable): array
    {
         return ['database','firebase'];
    }

    // Method to set the title dynamically
    private function getTitle()
    {
        return "Appointment Cancelled";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Appointment #' . $this->appointment->id .  ' cancelled due to no action within 24 hours.';
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم رفض الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'تم إلغاء الموعد رقم ' . $this->appointment->id . ' بسبب عدم اتخاذ أي إجراء خلال 24 ساعة.';
    }

        // Method to get token
    private function getToken()
    {        
        return $this->appointment->serviceProvider->user->firebase_token;       
      
    }


    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken();  //$notifiable->firebase_token;
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
                'body_ar' =>$this->getBodyAr(),
                'redirect_id' => (string) $this->appointment->id,
                'redirect_action' => 'appointments',
        ];
    }
}
