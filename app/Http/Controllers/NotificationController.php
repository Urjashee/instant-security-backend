<?php

namespace App\Http\Controllers;

use App\Common\FcmNotification;
use App\Common\ResponseFormatter;
use App\Common\StringTemplate;
use App\Constants;
use App\Models\DeviceTokens;
use App\Models\Notification;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NotificationController extends Controller
{
    public function addNotifications($job_id, $user_id, $notification_user_id, $type, $message)
    {
        $newNotifications = new Notification();
        $newNotifications->job_id = $job_id;
        $newNotifications->user_id = $user_id;
        $newNotifications->notification_user_id = $notification_user_id;
        $newNotifications->type = $type;
        $newNotifications->save();

        $tokens = DeviceTokens::where("user_id", $notification_user_id)
            ->whereNotNull("device_token")
            ->get();
        if ($tokens) {
            foreach ($tokens as $token) {
                try {
                    FcmNotification::fcmPushNotification(
                        $token->device_token,
                        StringTemplate::notifications($type),
                        $message,
                        $job_id,
                        $type
                    );
                } catch (\Exception $e) {
                    return ResponseFormatter::errorResponse($e->getMessage());
                }
            }
        }
    }

    public function getNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        $s3SiteName = Config::get('constants.s3_bucket');
        $contentsDecoded = [];
        $notificationData = [];
        $notifications = Notification::where("notification_user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where("type","!=", 5)
            ->orderBy("created_at", "desc")
            ->orderBy("read", "desc")
            ->get();
        if ($notifications) {
            foreach ($notifications as $notification) {
                $user_profile = UserProfile::where('user_id', $notification->user_id)->first();
                if ($user_profile)
                    $contentsDecoded [] = [
                        "notification_id" => $notification->id,
                        "notification_type_id" => $notification->type,
                        "notification_type" => StringTemplate::notifications($notification->type),
                        "guard_id" => $notification->user_id,
                        "guard_name" => $notification->user->first_name . " " . $notification->user->last_name,
                        "guard_image" => $user_profile->profile_image == null ? "" : $s3SiteName . $user_profile->profile_image,
                        "job_id" => $notification->job_id,
                        "title" => StringTemplate::notificationsTitle($notification->type, $notification->jobs->event_name),
                        "message" => StringTemplate::notificationsMessage($notification->type, $notification->user->first_name . " " . $notification->user->last_name),
                        "read" => $notification->read
                    ];
            }
            return ResponseFormatter::successResponse("Notifications", $contentsDecoded);
        } else {
            return ResponseFormatter::errorResponse("No notifications");
        }
    }

    public function countNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        $countNotifications = Notification::where("notification_user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where("read", 0)
            ->count();
        if ($countNotifications > 0) {
            return ResponseFormatter::successResponse("Notification count", $countNotifications);
        } else {
            return ResponseFormatter::errorResponse("No unread notification");
        }
    }

    public function readNotifications(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $getNotification = Notification::where("id", $id)
            ->where("read", 0)
            ->first();
        if ($getNotification) {
            $getNotification->read = 1;
            $getNotification->update();
            return ResponseFormatter::successResponse("Notification updated");
        } else {
            return ResponseFormatter::errorResponse("Notification not there");
        }
    }

//    This is for testing
    public function sendFcm(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $notification = FcmNotification::fcmPushNotification(
                $request->input("token"),
                $request->input("title"),
                $request->input("body"),
                $request->input("job_id"),
                $request->input("notification_type"));

            return ResponseFormatter::successResponse($notification);
        } catch (\Exception $e) {
            return ResponseFormatter::errorResponse($e->getMessage() . " error");
        }
    }
}
