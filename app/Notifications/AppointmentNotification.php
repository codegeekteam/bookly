<?php

namespace App\Notifications;
use App\Services\FirebaseNotification;
use GGInnovative\Larafirebase\Messages\FirebaseMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentNotification extends Notification implements ShouldQueue {

    use Queueable;

    public string $title;
    public string $body;
    public ?string $title_ar; // Optional Arabic fields
    public ?string $body_ar;

    public function __construct(string $title, string $body, ?string $title_ar = null, ?string $body_ar = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->title_ar = $title_ar; // Set Arabic title
        $this->body_ar = $body_ar;  // Set Arabic body
        $this->onQueue('default');
    }



    public function via($notifiable): array
    {
        return ['database','firebase'];
    }


    public function toFirebase($notifiable)
    {
        $fcm_token = $notifiable->firebase_token;
        return (new FirebaseNotification)
            ->withTitle($this->title)
            ->withBody($this->body)
            //->withAdditionalData()
            ->withToken($fcm_token)
            ->sendNotification();
    }

        public function toArray(object $notifiable): array
        {
            return [
                'title' => $this->title,
                'body' =>$this->body,
                'title_ar' => $this->title_ar,
                'body_ar' =>$this->body_ar,
                //'image_url' => $this->order->items->first()->product->thumbnail,
            ];

        }

}
