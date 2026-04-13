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

    

     /**
     * read sprecific notification
     *
     * endpoint to read logged in user one notification
     *
     * @type POST
     *
     * @url api/user/read-notification/{id}
     * @group notifications
     *
     * @authenticated
     *
     *
     * @response 200 { "message": 'notification mark as read }
     *
     */
    public function readSingleNotification(NotificationService $notificationService, $id)
    {
        try {

            return $notificationService->read_single(auth()->user(), $id);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    
     /**
     * get unread notifications count
     *
     * endpoint to get logged in user total number of unread notifications
     *
     * @type GET
     *
     * @url api/user/has-unread-notification
     *
     * @group notifications
     *
     * @response 200 { 'total unread notifications' : 20}
     */
    public function unreadNotificationsCount(NotificationService $notificationService)
    {
        try {

            return $notificationService->unreadNotificationsCount(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
