<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sender = $this->sender;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'message' => $this->message,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'sender' => $sender ? [
                'id' => $sender->id,
                'name' => $this->sender_type === 'customer'
                    ? $sender->first_name . ' ' . $sender->last_name
                    : $sender->name,
            ] : null,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
