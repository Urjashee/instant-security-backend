<?php
namespace App\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FcmNotification{
    public static function fcmPushNotification($firebaseToken, $title, $body, $job_id, $type)
    {
        //$SERVER_API_KEY = env('FCM_SERVER_KEY');

        $SERVER_API_KEY = Config::get('constants.firebase_server_key');

        $data = [
            "to" => $firebaseToken,
            "notification" => [
                "title" => "Instant Security",
                "body" => $body,
                "job_id" => $job_id,
                "notification_type" => $type,
            ],
            "data" => [
                "title" => "Instant Security",
                "body" => $body,
                "job_id" => $job_id,
                "notification_type" => $type,
            ]
        ];
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode( $data ),
                'header'=>  "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n" .
                    "Authorization:key=".$SERVER_API_KEY
            )
        );

        $context  = stream_context_create( $options );
        $result = file_get_contents( "https://fcm.googleapis.com/fcm/send", false, $context );
        return json_decode( $result );
    }
}

