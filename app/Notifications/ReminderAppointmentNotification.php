<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderAppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

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
         return ['database', 'firebase'];
    }

     // Method to set the title dynamically
    private function getTitle()
    {
        return 'Appointment confirm/reject reminder';
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Please confirm or reject the  appointment #'.$this->appointment->id.' with in ' . config('app.sub_hours') . ' hr. You cannot confirm or reject it after ' . config('app.sub_hours') . ' hour.';
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return 'تذكير بتأكيد/رفض الموعد';
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'يرجى تأكيد أو رفض الموعد رقم '.$this->appointment->id.' خلال ساعة واحدة. لا يمكنك تأكيده أو رفضه بعد مرور ساعة.';
    }

    // Method to get token
    private function getToken()
    {
        return $this->appointment->customer->user->firebase_token;
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
            
        ];
    }
}
