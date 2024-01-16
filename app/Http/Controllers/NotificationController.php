<?php

namespace App\Http\Controllers;

use App\Models\DeviceTokens;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function addNotifications($job_id, $user_id, $notification_user_id, $type) {
        $newNotifications = new Notification();
        $newNotifications->job_id = $job_id;
        $newNotifications->user_id = $user_id;
        $newNotifications->notification_user_id = $notification_user_id;
        $newNotifications->type = $type;
        $newNotifications->save();
//        $newNotifications->refresh();
//        $tokens = DeviceTokens::where("user_id",$notification_user_id)->get();
//        if ($tokens) {
//            foreach ($tokens as $token) {
//
//            }
//        }
    }

    public function getNotifications(Request $request) {

    }

    public function countNotifications(Request $request) {

    }

    public function readNotifications(Request $request) {

    }
}
