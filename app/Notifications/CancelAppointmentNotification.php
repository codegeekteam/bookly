<?php

namespace App\Notifications;
use App\Services\FirebaseNotification;
use Carbon\Carbon;
use GGInnovative\Larafirebase\Messages\FirebaseMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelAppointmentNotification extends Notification implements ShouldQueue {

    use Queueable;

    public $appointment;
    public $type;
    // public $goodWill;

    public function __construct($appointment, $type) 
    {
        $this->appointment = $appointment;
        $this->type = $type;    
        $this->onQueue('default');
    }


    public function via($notifiable): array
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
        if($this->type == 'customer')
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Your booking #' . $this->appointment->id . ' with ' . $this->appointment->serviceProvider->name . ' on ' . $serviceDate->format('l') . ', ' . $serviceDate->format('d-m-Y') . ' ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled. Refund will be processed within ' . config('app.refund_days') . ' days.';
            return  $messageText;
        } 
    
        if($this->type == 'provider'){
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Appointment #' . $this->appointment->id . ' of ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' on ' . $serviceDate->format('l') . ', ' . $serviceDate->format('d-m-Y') . ' ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled.' ;
            return $messageText;
        }
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم الغاء الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
         if($this->type == 'customer')
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'تم إلغاء حجزك رقم ' . $this->appointment->id . ' مع ' . $this->appointment->serviceProvider->name . ' في ' . $serviceDate->format('l') . ', ' . $serviceDate->format('d-m-Y') . ' ' .$this->appointment->services[0]->pivot->start_time . '. سيتم استرداد المبلغ خلال ' . config('app.refund_days') . ' أيام.';
            return  $messageText;
        }

        if($this->type == 'provider'){
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'تم إلغاء الموعد رقم ' . $this->appointment->id . ' الخاص بالعميل ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' بتاريخ ' . $serviceDate->format('l') . ' ' . $serviceDate->format('d-m-Y') . ', ' .$this->appointment->services[0]->pivot->start_time . '.' ;
            return $messageText;
        }
    }

     // Method to get token
    private function getToken($type)
    {
        if($type == 'customer') {
            return $this->appointment->customer->user->firebase_token;
        }
        if($type == 'provider') {
            return $this->appointment->serviceProvider->user->firebase_token;
        }
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken($this->type); //$notifiable->firebase_token;
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
                'body_ar' =>$this->getBodyAr(),
                'redirect_id' => (string) $this->appointment->id,
                'redirect_action' => 'appointments',
                //'image_url' => $this->order->items->first()->product->thumbnail,
            ];

        }

}
