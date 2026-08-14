<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Services\FirebaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PayoutCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $payout;

    /**
     * Create a new notification instance.
     */
    public function __construct($payout)
    {
        $this->payout = $payout;
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
        return "Payout Cancelled";
    }

    // Method to set the body dynamically
    private function getBody()
    {
        return 'Payout Cancelled #' . $this->payout->id;
    }

    // Method to set the title ar dynamically
    private function getTitleAr()
    {
        return "تم إلغاء الصرف";
    }

    // Method to set the body ar dynamically
    private function getBodyAr()
    {
        return 'تم إلغاء الدفعة #' . $this->payout->id;
    }

    // Method to get token
    private function getToken()
    {
       // return $this->payout->serviceProvider->user->firebase_token;
       $token = $this->payout->serviceProvider->user->firebase_token;

    if (!$token || trim($token) === '') {
        \Log::error('Missing Firebase token', [
            'user_id' => $this->payout->serviceProvider->user->id ?? null,
        ]);
        return null;
    }

    return trim($token);

    }


    public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken(); //$notifiable->firebase_token;
        if (!$fcm_token) {
    \Log::error('FCM send aborted: empty token');
    return;
}
        \Log::info('FCM Token: ' . $fcm_token);
        return (new FirebaseNotification)
            ->withTitle($this->getTitle())
            ->withBody($this->getBody())
            ->withAdditionalData([
                'redirect_id' => (string) $this->payout->id,
                'redirect_action' => 'payouts',
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
            'redirect_id' => (string) $this->payout->id,
            'redirect_action' => 'payouts',
        ];
    }
}
