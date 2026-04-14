<?php

namespace App\Notifications;

use App\Services\FirebaseNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestCancellationCustomerNotification extends Notification implements ShouldQueue 
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
        return ['database','firebase'];
    }

     // Method to set the title dynamically
    private function getTitle()
    {
        return "Appointment Cancellation Requested";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        // if($this->type == 'customer')
        // {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Provider initiated cancellation for your booking #' . $this->appointment->id . ' with ' . $this->appointment->serviceProvider->name . ' on ' . $serviceDate->format('l') . ', ' . $serviceDate->format('d-m-Y') . ' ' .$this->appointment->services[0]->pivot->start_time . '. The refund will be processed once the cancellation is completed by the administrator.';
            return  $messageText;
        // } 
    
        // if($this->type == 'provider'){
        //     $date = $this->appointment->services[0]->pivot->date ?? now();
        //     $serviceDate = Carbon::parse($date);
        //     $messageText = 'Appointment #' . $this->appointment->id . ' of ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' on ' . $serviceDate->format('l') . ' , ' . $serviceDate->format('d-m-Y') . '  ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled. ' ;
        //     return $messageText;
        // }
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم الغاء الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        //  if($this->type == 'customer')
        // {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'لقد قام مقدم الخدمة بإلغاء حجزك رقم: ' . $this->appointment->id . ' مع ' . $this->appointment->serviceProvider->name . '، وذلك في يوم ' . $serviceDate->format('l') . '، بتاريخ ' . $serviceDate->format('d-m-Y') . ' في تمام الساعة ' .$this->appointment->services[0]->pivot->start_time . '. سيتم البدء في إجراءات استرداد المبلغ بمجرد إتمام عملية الإلغاء من قِبَل المسؤول.';
            return  $messageText;
        // }

        // if($this->type == 'provider'){
        //     $date = $this->appointment->services[0]->pivot->date ?? now();
        //     $serviceDate = Carbon::parse($date);
        //     $messageText = 'تم إلغاء الموعد رقم ' . $this->appointment->id . ' الخاص بالعميل ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' بتاريخ ' . $serviceDate->format('l') . '  ' . $serviceDate->format('d-m-Y') . ' , ' .$this->appointment->services[0]->pivot->start_time . '. ' ;
        //     return $messageText;
        // }
    }

     // Method to get token
    private function getToken($type)
    {
       // if($type == 'customer') {
            return $this->appointment->customer->user->firebase_token;
        // }
        // if($type == 'provider') {
        //     return $this->appointment->serviceProvider->user->firebase_token;
        // }
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken('customer'); // $this->getToken('$this->type'); //$notifiable->firebase_token;
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
