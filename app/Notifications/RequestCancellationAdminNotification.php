<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestCancellationAdminNotification extends Notification implements ShouldQueue 
{
    use Queueable;

    public $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

      private function getTitle()
    {
        return "Appointment cancellation requested";
    }

    // Method to set the body dynamically
    private function getBody()
    {        
        $date = $this->appointment->services[0]->pivot->date ?? now();
        $serviceDate = Carbon::parse($date);
        $messageText = 'Provider : ' . $this->appointment->serviceProvider->name . ' requested cancellation for the booking #' . $this->appointment->id . ' of ' . $this->appointment->customer->first_name . ' ' . $this->appointment->customer->last_name . ' on ' . $serviceDate->format('l') . ', ' . $serviceDate->format('d-m-Y') . ' ' . $this->appointment->services[0]->pivot->start_time ;
        return  $messageText;   
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم الغاء الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {      
        $date = $this->appointment->services[0]->pivot->date ?? now();
        $serviceDate = Carbon::parse($date);
        $messageText = 'مقدم الخدمة: ' . $this->appointment->serviceProvider->name . ' طلب إلغاء الحجز رقم ' . $this->appointment->id . ' الخاص بـ ' . $this->appointment->customer->first_name . ' ' . $this->appointment->customer->last_name . ' في يوم ' . $serviceDate->format('l') . '، بتاريخ ' . $serviceDate->format('d-m-Y') . '، في تمام الساعة ' . $this->appointment->services[0]->pivot->start_time ;
        return  $messageText;
   
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
