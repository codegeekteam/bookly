<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatConversationResource;
use App\Http\Resources\ChatMessageResource;
use App\Models\Appointment;
use App\Models\ChatConversation;
use App\Models\Customer;
use App\Models\ServiceProvider;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Send a message in a conversation
     *
     * @authenticated
     * @group Chat
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $user = auth()->user();
            $appointment = Appointment::with('conversation')->find($request->appointment_id);

            // Check if user can access this appointment
            if (!$this->canAccessAppointment($appointment, $user)) {
                return $this->error('You do not have access to this appointment');
            }

            // Check if chat is allowed for this appointment
            if (!$this->chatService->canChat($appointment)) {
                return $this->error('Chat is not available for this appointment. Appointment must be confirmed.');
            }

            // Create conversation if it doesn't exist
            if (!$appointment->conversation) {
                $conversation = $this->chatService->createConversation($appointment);
            } else {
                $conversation = $appointment->conversation;
            }

            // Check if conversation is active
            if (!$conversation->is_active) {
                return $this->error('This conversation has been deactivated');
            }

            // Determine sender type and ID
            if ($user->customer) {
                $senderType = 'customer';
                $senderId = $user->customer->id;
            } elseif ($user->serviceProvider) {
                $senderType = 'provider';
                $senderId = $user->serviceProvider->id;
            } else {
                return $this->error('Invalid user type');
            }

            // Send the message
            $message = $this->chatService->sendMessage(
                $conversation,
                $request->message,
                $senderType,
                $senderId
            );

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => new ChatMessageResource($message)
            ], 200);
        } catch (\Exception $e) {
            return $this->error('Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Get conversation with messages
     *
     * @authenticated
     * @group Chat
     */
    public function getConversation(Request $request, $appointmentId)
    {
        try {
            $user = auth()->user();
            $appointment = Appointment::find($appointmentId);

            if (!$appointment) {
                return $this->error('Appointment not found');
            }

            // Check if user can access this appointment
            if (!$this->canAccessAppointment($appointment, $user)) {
                return $this->error('You do not have access to this appointment');
            }

            // Get the user entity (Customer or ServiceProvider)
            $userEntity = $user->customer ?: $user->serviceProvider;

            $conversation = $this->chatService->getConversation($appointmentId, $userEntity);

            if (!$conversation) {
                // Create conversation if payment is successful
                if ($this->chatService->canChat($appointment)) {
                    $conversation = $this->chatService->createConversation($appointment);
                    $conversation->load('messages');
                } else {
                    return $this->error('Chat is not available for this appointment');
                }
            }

            // Mark all messages as read for the current user
            $this->chatService->markMessagesAsRead($conversation, $userEntity);

            // Paginate messages
            $messages = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation' => new ChatConversationResource($conversation),
                    'messages' => ChatMessageResource::collection($messages),
                    'pagination' => [
                        'total' => $messages->total(),
                        'per_page' => $messages->perPage(),
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                    ],
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->error('Failed to get conversation: ' . $e->getMessage());
        }
    }

    /**
     * Get all conversations for the authenticated user
     *
     * @authenticated
     * @group Chat
     */
    public function getConversations(Request $request)
    {
        try {
            $user = auth()->user();
            $userEntity = $user->customer ?: $user->serviceProvider;

            if (!$userEntity) {
                return $this->error('Invalid user type');
            }

            $conversations = $this->chatService->getConversationsForUser($userEntity);

            return response()->json([
                'success' => true,
                'data' => ChatConversationResource::collection($conversations)
            ], 200);
        } catch (\Exception $e) {
            return $this->error('Failed to get conversations: ' . $e->getMessage());
        }
    }

    private function canAccessAppointment(Appointment $appointment, $user): bool
    {
        if ($user->customer && $appointment->customer_id === $user->customer->id) {
            return true;
        }

        if ($user->serviceProvider && $appointment->service_provider_id === $user->serviceProvider->id) {
            return true;
        }

        return false;
    }
}