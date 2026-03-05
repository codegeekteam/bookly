<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRescheduledNotification extends Notification implements ShouldQueue 
{
    use Queueable;

    public $appointment;
    public $reschedule_date;
    public $reschedule_time;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment, $reschedule_date, $reschedule_time)
    {
        $this->appointment = $appointment;
        $this->reschedule_date = $reschedule_date;
        $this->reschedule_time = $reschedule_time;
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
        return "Appointment Rescheduled";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Your appointment #' . $this->appointment->id . ' has been rescheduled. Date : '  . $this->reschedule_date . ' Time slots : ' . $this->reschedule_time;
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم إعادة جدولة الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'تم طلب تعديل موعد رقم :  ' . $this->appointment->id . 'الى : ' . $this->reschedule_time . '  ' . $this->reschedule_date;
        return 'تم إعادة جدولة موعدك رقم' . $this->appointment->id . '. التاريخ: '  . $this->reschedule_date . '، الأوقات المتاحة: '  . $this->reschedule_time ;
    }

    // Method to get token
    private function getToken()
    {
        return $this->appointment->customer->user->firebase_token;
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken(); //$notifiable->firebase_token;
        \Log::info('FCM Token: ' . $notifiable->firebase_token);
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
