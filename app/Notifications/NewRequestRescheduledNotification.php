<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequestRescheduledNotification extends Notification implements ShouldQueue 
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
        return "Customer rescheduled appointment";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Appointment #' . $this->appointment->id . ' is rescheduled by customer. Date : '  . $this->reschedule_date . ', Timeslots : ' . $this->reschedule_time;
    }

   // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "قام العميل بإعادة جدولة موعده";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'تم إعادة جدولة الموعد رقم'. $this->appointment->id .' من قبل العميل. التاريخ: ' . $this->reschedule_date . '، الأوقات المتاحة: ' . $this->reschedule_time ;
    }

    // Method to get token
    private function getToken()
    {
        return $this->appointment->serviceProvider->user->firebase_token;
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken(); 
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
