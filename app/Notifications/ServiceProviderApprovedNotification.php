<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\FirebaseNotification;

class ServiceProviderApprovedNotification extends Notification
{
    use Queueable;

    public $serviceProvider;
    public $active;

    /**
     * Create a new notification instance.
     */
    public function __construct($serviceProvider, $active)
    {
        $this->serviceProvider = $serviceProvider;
        $this->active = $active;
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


    private function getTitle($active)
    {
        if($active) {
             return 'Account Activativation Message';
        }else {
             return 'Account Deactivativation Message';
        }
    }

       // Method to set the body dynamically
    private function getBody($active)
    {
        if($active) {
             return 'Your vendor account has been activated. You can now log in and start using the platform.';
        }else {
             return 'Your account has been temporarily deactivated. Please contact support.';
        }
       
    }

    // Method to set the title ar dynamically
    private function getTitleAr($active)
    {
        if($active) {
             return 'رسالة تفعيل الحساب';
        }else {
             return 'رسالة إلغاء تنشيط الحساب';
        }
    }

    // Method to set the body ar dynamically
    private function getBodyAr($active)
    {
        if($active) {
             return 'تم تفعيل حساب المورّد الخاص بك. يمكنك الآن تسجيل الدخول والبدء في استخدام المنصة.';
        }else {
             return 'تم تعطيل حسابك مؤقتاً. يرجى التواصل مع الدعم الفني.';
        }
    }


           // Method to get token
    private function getToken()
    {        
        return $this->serviceProvider->user->firebase_token;       
      
    }

     public function toFirebase($notifiable)
    {
        $fcm_token = $this->getToken();  //$notifiable->firebase_token;
        return (new FirebaseNotification)
            ->withTitle($this->getTitle($this->active))
            ->withBody($this->getBody($this->active))
            ->withAdditionalData([
                'redirect_id' => (string) $this->serviceProvider->id,
                'redirect_action' => 'login',
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
            'title' => $this->getTitle($this->active),
            'body' => $this->getBody($this->active),
            'title_ar' => $this->getTitleAr($this->active),
            'body_ar' =>$this->getBodyAr($this->active),
            'redirect_id' => (string) $this->serviceProvider->id,
            'redirect_action' => 'login',
        ];
    }
}
