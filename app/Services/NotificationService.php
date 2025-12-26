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

}
