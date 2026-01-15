<?php

namespace App\Notifications;
use App\Services\FirebaseNotification;
use GGInnovative\Larafirebase\Messages\FirebaseMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoyaltyPointTransactionNotification extends Notification implements ShouldQueue {

    use Queueable;

    public string $body;
    public ?string $body_ar;

    public function __construct( string $body, ?string $body_ar = null)
    {
        $this->body = $body;
        $this->body_ar = $body_ar;  // Set Arabic body
        $this->onQueue('default');
    }



    public function via($notifiable): array
    {
        return ['database','firebase'];
    }

    // Method to set the title dynamically
    private function getTitle()
    {
        return "your Loyalty Points updated";
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تحديث في نقاط الولاء الخاصة بك";
    }

    public function toFirebase($notifiable)
    {
        $fcm_token = $notifiable->firebase_token;
        return (new FirebaseNotification)
            ->withTitle($this->getTitle())
            ->withBody($this->body)
            //->withAdditionalData()
            ->withToken($fcm_token)
            ->sendNotification();
    }

        public function toArray(object $notifiable): array
        {
            return [
                'title' => $this->getTitle(),
                'body' =>$this->body,
                'title_ar' => $this->getTitleAr(),
                'body_ar' =>$this->body_ar,
                //'image_url' => $this->order->items->first()->product->thumbnail,
            ];

        }

}
