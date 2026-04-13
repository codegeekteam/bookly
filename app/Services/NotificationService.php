<?php

namespace App\Services;

use App\Http\Resources\Api\NotificationResource;
use App\Models\User;

use App\Traits\HandleResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationService
{
    use HandleResponse;
    public function index(User $user)
    {
        $notifications=$user->notifications()->paginate(10);
        return NotificationResource::collection($notifications);
    }

    public function read_all(User $user)
    {
        $notifications=$user->notifications()->whereNull('read_at')->update(['read_at'=>Carbon::now()]);
        return response()->json(['message' => 'notifications mark as read'], 200);
    }
    
    public function read_single(User $user, $id)
    {
        $notifications=$user->notifications()->whereNull('read_at')->where('id', $id)->update(['read_at'=>Carbon::now()]);
        return response()->json(['message' => 'notification mark as read'], 200);
    }

    public function unreadNotificationsCount(User $user)
    {
        $notifications=$user->notifications()->whereNull('read_at')->count();
        // return NotificationResource::collection($notifications);
        return response()->json(['total unread notifications' => $notifications ?? 0]);
    }

}
