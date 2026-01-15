<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $userEntity = $user ? ($user->customer ?: $user->serviceProvider) : null;

        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->first_name . ' ' . $this->customer->last_name,
                'phone' => $this->customer->phone_number,
            ],
            'service_provider' => [
                'id' => $this->serviceProvider->id,
                'name' => $this->serviceProvider->name,
                'phone' => $this->serviceProvider->phone_number,
            ],
            'is_active' => $this->is_active,
            'latest_message' => $this->whenLoaded('latestMessage', function () {
                return $this->latestMessage ? [
                    'id' => $this->latestMessage->id,
                    'message' => $this->latestMessage->message,
                    'sender_type' => $this->latestMessage->sender_type,
                    'created_at' => $this->latestMessage->created_at->toISOString(),
                ] : null;
            }),
            'unread_count' => $userEntity ? $this->getUnreadCountForUser($userEntity) : 0,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
