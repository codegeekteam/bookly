<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat conversation channel authorization
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\ChatConversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // Handle Customer model directly (from API authentication)
  /*  if ($user instanceof \App\Models\Customer) {
        return $conversation->customer_id === $user->id;
    }

    // Handle ServiceProvider model directly
    if ($user instanceof \App\Models\ServiceProvider) {
        return $conversation->service_provider_id === $user->id;
    }

    // Handle User model with relationships (for web authentication if needed)
    if ($user instanceof \App\Models\User) {
        if ($user->customer && $conversation->customer_id === $user->customer->id) {
            return true;
        }

        if ($user->serviceProvider && $conversation->service_provider_id === $user->serviceProvider->id) {
            return true;
        }
    } */

       //By sreeja
        return
        ($user->customer && $conversation->customer_id === $user->customer->id)
        || ($user->serviceProvider && $conversation->service_provider_id === $user->serviceProvider->id);
    // ends here

    return false;
});
