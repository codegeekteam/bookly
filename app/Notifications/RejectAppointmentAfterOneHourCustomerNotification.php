<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectAppointmentAfterOneHourCustomerNotification extends Notification implements ShouldQueue
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
        $date = $this->appointment->services[0]->pivot->date ?? now();
        $serviceDate = Carbon::parse($date);
        return 'Your appointment #' . $this->appointment->id .  ' with ' . $this->appointment->serviceProvider->name . ' on ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' . $this->appointment->services[0]->pivot->start_time . ' has been cancelled due to no action. You will be refunded the full deposit amount of  SAR ' . $this->appointment->deposit_amount . ' to your bank account within ' . config('app.refund_days') . ' days';
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم رفض الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        $date = $this->appointment->services[0]->pivot->date ?? now();
        $serviceDate = Carbon::parse($date);
        return 'تم إلغاء موعدك رقم ' . $this->appointment->id . ' مع ' . $this->appointment->serviceProvider->name . ' بتاريخ ' . $serviceDate->format('l') . ' و ' . $serviceDate->format('d-m-Y') . ' و ' .$this->appointment->services[0]->pivot->start_time . ' لعدم اتخاذ أي إجراء. سيتم رد مبلغ التأمين بالكامل وقدره ' . $this->appointment->deposit_amount . ' ريال سعودي إلى حسابك البنكي خلال 7 أيام';
    }

        // Method to get token
    private function getToken()
    {        
        return $this->appointment->customer->user->firebase_token;       
      
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
            //'image_url' => $this->order->items->first()->product->thumbnail,
        ];
    }
}
