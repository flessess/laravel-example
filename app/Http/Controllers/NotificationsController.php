<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Events\NotificationDeletedEvent;
use App\Events\NotificationsAllReadEvent;
use App\Events\NotificationsFlushedEvent;
use App\Http\Requests\NotificationsRequest;
use App\Models\DatabaseNotification;
use App\Models\LongReport as LongReportModel;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class NotificationsController.
 *
 * @package App\Http\Controllers
 */
class NotificationsController extends Controller
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
        $notifications = [];

        // TODO uncomment after laravel v.5.5 release
        // foreach ($request->user()->unreadNotifications() as $notification) {
        //
        // temporary workaround
        $databaseNotifications = DatabaseNotification::
            where([
                'notifiable_id' => $request->user()->getKey(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($databaseNotifications as $notification) {
            if (!isset($notification->data['eventType'])) {
                continue;
            }

            $read = $notification->read_at !== null;
            $eventType = $notification->data['eventType'] ?? false;
            $url = $notification->data['url'] ?? false;

            $type = "pdf";
            $text = trans('common.datatables.pdf_export_generated');
            if (isset($url) && Str::endsWith($url, '.xlsx')
                  || (substr($notification->data['eventType'], 0, 5) == 'Excel')) {
                $text = trans('common.datatables.xls_export_generated');
                $type = "excel";
            }

            if ($read) {
                 $text = strtoupper($type)." has been downloaded.";
            }

            $subject = $notification->data['subject'] ?? false;
            if(!$subject) {
                $subject = $this->getDocumentTitle($url);
            }
            $date = $notification->data['date'] ?? false;

            $notifications[] = [
                'id' => $notification->id ?? false,
                'url' => $url,
                'filesize' => $notification->data['filesize'] ?? false,
                'path' => $notification->data['path'] ?? false,
                'eventType' => $eventType,
                'requestTime' => $notification->data['requestTime'] ?? false,
                'read' => $notification->read_at !== null,
                'text' => $text,
                'html' => ($notification->data['html'] ?? ''),
                'messageType' => $notification->data['messageType'] ?? false,
                'subject' => $subject,
                'date' => $date,
                'fileType' => $type,
            ];
        }

        return response()->json($notifications);
    }

    /**
     * @return $this
     */
    public function notificationsPage()
    {
        $user = Auth::user();

        $pageTitle = 'Notifications';
        $fullName = $user->getFullName();

        return view('notifications.notifications', compact('fullName', 'pageTitle'));
    }

    /**
     * @param NotificationsRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(NotificationsRequest $request)
    {
        $id = $request->input('id');

        try {
            $count = DatabaseNotification::
                where([
                    'notifiable_id' => $request->user()->getKey(),
                    'id' => $id,
                ])
                ->delete();
            if ($count) {
                event(new NotificationDeletedEvent($id));

                return $this->buildSimpleJsonSuccessMessage("Notification deleted successfully");
            }

            return $this->buildSimpleJsonError("Notification not found");
        } catch (Exception $e) {
            logException($e);
            return $this->buildSimpleJsonError($e->getMessage());
        }
    }

    /**
     * @param NotificationsRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function flush(NotificationsRequest $request)
    {
        try {
            $count = DatabaseNotification::
                where([
                    'notifiable_id' => $request->user()->getKey(),
                ])
                ->delete();
            if ($count) {
                event(new NotificationsFlushedEvent());

                return $this->buildSimpleJsonSuccessMessage("All notifications deleted successfully");
            }

            return $this->buildSimpleJsonError("Notifications not found");
        } catch (Exception $e) {
            logException($e);
            return $this->buildSimpleJsonError($e->getMessage());
        }
    }

    /**
     * @param NotificationsRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function readAll(NotificationsRequest $request)
    {
        try {
            $count = DatabaseNotification::
                where([
                    'notifiable_id' => $request->user()->getKey(),
                    'read_at' => null,
                ])
                ->update(['read_at' => Carbon::now()]);

            if ($count) {
                event(new NotificationsAllReadEvent());

                return $this->buildSimpleJsonSuccessMessage("All notifications marked as read");
            }

            return $this->buildSimpleJsonError("Notifications not found");
        } catch (Exception $e) {
            logException($e);
            return $this->buildSimpleJsonError($e->getMessage());
        }
    }

    public function getDocumentTitle($url)
    {
        try {
            $urls = parse_url($url);
            $filename = pathinfo($urls["path"])["filename"];

            $arr = explode("_for_", $filename);

            return ucwords(str_replace("_", " ", $arr[0]));
        } catch (Exception $ex) {
            logException($ex);
            return "No Title";
        }
    }
}
