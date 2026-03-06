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
     public $goodWill;

    public function __construct($appointment, $type, $goodWill)
    {
        $this->appointment = $appointment;
        $this->type = $type;
        $this->goodWill = $goodWill;
        $this->onQueue('default');
    }


    public function via($notifiable): array
    {
        return ['database','firebase'];
    }

    // Method to set the title dynamically
    private function getTitle()
    {
        return "Appointment cancelled";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        if($this->type == 'customer' && $this->goodWill)
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Your booking #' . $this->appointment->id . ' with ' . $this->appointment->serviceProvider->name . ' on ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled due to '. str_replace('_', ' ' , $this->appointment->admin_cancel_reason) . '. You will be refunded the full deposit amount of SAR ' . $this->appointment->total_payed . ' to your bank account within ' . config('app.refund_days') . ' days.
            Because you’re a valued customer, we have transferred SAR ' . $this->appointment->goodwill_amount  . ' to your app wallet to use for your next booking.';
            return  $messageText;
        } 
        if($this->type == 'customer' && !($this->goodWill))
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Your booking #' . $this->appointment->id . ' with ' . $this->appointment->serviceProvider->name . ' on ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled due to '. str_replace('_', ' ' , $this->appointment->admin_cancel_reason) . '. You will be refunded the full deposit amount of SAR ' . $this->appointment->total_payed . ' to your bank account within ' . config('app.refund_days') . ' days.';
            return  $messageText;
        } 
        if($this->type == 'provider'){
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'Appointment #' . $this->appointment->id . ' of ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' on ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' has been cancelled due to '. str_replace("_", " "  , $this->appointment->admin_cancel_reason) . '.' ;
            return $messageText;
        }
    }

    // Method to set the title ar dynamically
    private function getTitleAr($typ)
    {
        return "تم الغاء الموعد";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
         if($this->type == 'customer' && $this->goodWill)
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'تم إلغاء حجزك رقم ' . $this->appointment->id . ' مع ' . $this->appointment->serviceProvider->name . ' بتاريخ ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' بسبب '. str_replace('_', ' ' , $this->appointment->admin_cancel_reason) . '. سيتم رد مبلغ التأمين بالكامل وقدره ' . $this->appointment->total_payed . ' إلى حسابك البنكي خلال ' . config('app.refund_days') . ' يومًا.
... لأنك عميل مميز، قمنا بتحويل مبلغ ' . $this->appointment->goodwill_amount . ' إلى محفظة التطبيق الخاصة بك لاستخدامه في حجزك القادم.';
            return  $messageText;
        } 
        if($this->type == 'customer' && !($this->goodWill))
        {
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'تم إلغاء حجزك رقم ' . $this->appointment->id . ' مع ' . $this->appointment->serviceProvider->name . ' بتاريخ ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' بسبب '. str_replace('_', ' ' , $this->appointment->admin_cancel_reason) . '. سيتم رد مبلغ التأمين بالكامل وقدره ' . $this->appointment->total_payed . ' إلى حسابك البنكي خلال ' . config('app.refund_days') . ' أيام.';
            return  $messageText;
        } 
        if($this->type == 'provider'){
            $date = $this->appointment->services[0]->pivot->date ?? now();
            $serviceDate = Carbon::parse($date);
            $messageText = 'تم إلغاء الموعد رقم ' . $this->appointment->id . ' الخاص بالعميل ' . $this->appointment->customer->first_name . " " . $this->appointment->customer->last_name . ' بتاريخ ' . $serviceDate->format('l') . ' & ' . $serviceDate->format('d-m-Y') . ' & ' .$this->appointment->services[0]->pivot->start_time . ' بسبب '. str_replace("_", " " , $this->appointment->admin_cancel_reason) . '.';
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
