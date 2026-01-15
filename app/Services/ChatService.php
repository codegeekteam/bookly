<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Events\MessageSent;
use App\Models\Appointment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;

class ChatService
{
    protected FirebaseNotification $firebaseNotification;

    public function __construct(FirebaseNotification $firebaseNotification)
    {
        $this->firebaseNotification = $firebaseNotification;
    }

    public function createConversation(Appointment $appointment): ChatConversation
    {
        return ChatConversation::firstOrCreate([
            'appointment_id' => $appointment->id,
        ], [
            'customer_id' => $appointment->customer_id,
            'service_provider_id' => $appointment->service_provider_id,
            'is_active' => true,
        ]);
    }

    public function sendMessage(
        ChatConversation $conversation,
        string $message,
        string $senderType,
        int $senderId
    ): ChatMessage {
        DB::beginTransaction();
        try {
            $chatMessage = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'message' => $message,
            ]);

               DB::commit();  //by Sreeja

            // Broadcast the message via Reverb
            broadcast(new MessageSent($chatMessage))->toOthers();

            // Send Firebase notification to the receiver
            $this->sendNotificationToReceiver($chatMessage, $conversation);

          //  DB::commit(); //by Sreeja

            return $chatMessage;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function sendNotificationToReceiver(ChatMessage $message, ChatConversation $conversation): void
    {
        $receiver = null;
        $senderName = '';

        if ($message->sender_type === 'customer') {
            // Send to provider
            $receiver = $conversation->serviceProvider;
            $customer = Customer::find($message->sender_id);
            $senderName = $customer->first_name . ' ' . $customer->last_name;
        } else {
            // Send to customer
            $receiver = $conversation->customer;
            $provider = ServiceProvider::find($message->sender_id);
            $senderName = $provider->name;
        }

        if ($receiver && $receiver->user && $receiver->user->device_token) {
            $this->firebaseNotification
                ->withTitle('New Message from ' . $senderName)
                ->withBody(substr($message->message, 0, 100))
                ->withAdditionalData([
                    'type' => 'chat_message',
                    'conversation_id' => $conversation->id,
                    'appointment_id' => $conversation->appointment_id,
                ])
                ->withToken($receiver->user->device_token)
                ->sendNotification();
        }
    }

    public function getConversation(int $appointmentId, $user): ?ChatConversation
    {
        $conversation = ChatConversation::where('appointment_id', $appointmentId)
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->first();

        if (!$conversation || !$this->canAccessConversation($conversation, $user)) {
            return null;
        }

        return $conversation;
    }

    public function canAccessConversation(ChatConversation $conversation, $user): bool
    {
        if ($user instanceof Customer) {
            return $conversation->customer_id === $user->id;
        } elseif ($user instanceof ServiceProvider) {
            return $conversation->service_provider_id === $user->id;
        }
        return false;
    }

    public function canChat(Appointment $appointment): bool
    {
        // Chat is only allowed when appointment is confirmed
        return $appointment->status_id === AppointmentStatus::Confirmed->value;
    }

    public function deactivateConversation(int $appointmentId): void
    {
        ChatConversation::where('appointment_id', $appointmentId)
            ->update(['is_active' => false]);
    }

    public function getConversationsForUser($user)
    {
        $query = ChatConversation::with([
            'appointment',
            'customer',
            'serviceProvider',
            'latestMessage'
        ])->where('is_active', true);

        if ($user instanceof Customer) {
            $query->where('customer_id', $user->id);
        } elseif ($user instanceof ServiceProvider) {
            $query->where('service_provider_id', $user->id);
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    public function markMessagesAsRead(ChatConversation $conversation, $user): void
    {
        if ($user instanceof Customer) {
            $receiverType = 'customer';
            $receiverId = $user->id;
        } elseif ($user instanceof ServiceProvider) {
            $receiverType = 'provider';
            $receiverId = $user->id;
        } else {
            return;
        }

        // Mark all unread messages for this user as read
        $conversation->messages()
            ->unreadFor($receiverType, $receiverId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}