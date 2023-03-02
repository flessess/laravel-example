<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationsRequest;
use App\Helpers\MaintenanceNotifications as MaintenanceNotificationsHelper;

/**
 * Class MaintenanceNotificationController.
 *
 * @package App\Http\Controllers
 */
class MaintenanceNotificationsController extends Controller
{
    /**
     * Get Notifications.
     *
     * @param \App\Http\Requests\NotificationsRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(NotificationsRequest $request)
    {

        $notifications = MaintenanceNotificationsHelper::getFromCache();

        return response()->json($notifications);
    }

}
