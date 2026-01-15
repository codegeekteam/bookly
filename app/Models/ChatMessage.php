<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

   /* public function getSenderAttribute()
    {
        if ($this->sender_type === 'customer') {
            return Customer::find($this->sender_id);
        } elseif ($this->sender_type === 'provider') {
            return ServiceProvider::find($this->sender_id);
        }
        return null;
    } */

    //By sreeja
    public function sender(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'sender_type',
            'sender_id'
        );
    }

    public function isFromCustomer(): bool
    {
        return $this->sender_type === 'customer';
    }

    public function isFromProvider(): bool
    {
        return $this->sender_type === 'provider';
    }

    public function scopeUnreadFor($query, string $receiverType, int $receiverId)
    {
       /* return $query->where('is_read', false)
            ->where(function ($q) use ($receiverType, $receiverId) {
                // Messages not sent by the receiver (i.e., received by them)
                $q->where('sender_type', '!=', $receiverType)
                    ->orWhere('sender_id', '!=', $receiverId);
            }); */

        //By sreeja
        return $query
        ->where('is_read', false)
        ->where(function ($q) use ($receiverType, $receiverId) {
            $q->where('sender_type', '!=', $receiverType)
              ->where('sender_id', '!=', $receiverId);
        });
    }
}