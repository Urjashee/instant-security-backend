<?php

namespace App\Http\Controllers;

use App\Common\FcmNotification;
use App\Common\ResponseFormatter;
use App\Models\DeviceTokens;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function addNotifications($job_id, $user_id, $notification_user_id, $type, $message) {
        $newNotifications = new Notification();
        $newNotifications->job_id = $job_id;
        $newNotifications->user_id = $user_id;
        $newNotifications->notification_user_id = $notification_user_id;
        $newNotifications->type = $type;
        $newNotifications->save();
//        $newNotifications->refresh();
        $tokens = DeviceTokens::where("user_id",$notification_user_id)->get();
        if ($tokens) {
            foreach ($tokens as $token) {
                try {
                    FcmNotification::fcmPushNotification(
                        $token->device_token,
                        "Clock In",
                        $message);
                } catch (\Exception $e) {
                    return ResponseFormatter::errorResponse($e->getMessage());
                }
            }
        }
    }

    public function getNotifications(Request $request) {

    }

    public function countNotifications(Request $request) {

    }

    public function readNotifications(Request $request) {

    }

    public function sendFcm(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $notification = FcmNotification::fcmPushNotification(
                $request->input("token"),
                $request->input("title"),
                $request->input("body"));

            return ResponseFormatter::successResponse($notification);
        } catch (\Exception $e) {
            return ResponseFormatter::errorResponse($e->getMessage());
        }
    }
}
