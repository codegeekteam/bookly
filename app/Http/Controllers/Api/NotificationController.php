<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    /**
     * get notifications
     *
     * endpoint to get logged in user notifications
     *
     * @type GET
     *
     * @url api/user/notifications
     *
     * @group notifications
     *
     * @response 200 [ { "id": "443425d7-22bd-4a8d-bcff-3c98f1115ada", "type": "App\\Notifications\\TestNotification", "data": { "title": "Test Notification", "body": "This is a test notification"}, "read_at": null, "created_at": "2023-11-07T12:38:43.000000Z"} ]
     */
    public function index(NotificationService $notificationService)
    {
        try {

            return $notificationService->index(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * read notifications
     *
     * endpoint to read all logged in user notifications
     *
     * @type POST
     *
     * @url api/user/read-all-notifications
     * @group notifications
     *
     * @authenticated
     *
     *
     * @response 200 { "message": 'notifications mark as read }
     *
     */
    public function readAll(NotificationService $notificationService)
    {
        try {

            return $notificationService->read_all(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
