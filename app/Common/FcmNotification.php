<?php
namespace App\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FcmNotification{
    public static function fcmPushNotification($firebaseToken, $title, $body, $job_id, $type): string
    {
        //$SERVER_API_KEY = env('FCM_SERVER_KEY');

        $SERVER_API_KEY = Config::get('constants.firebase_server_key');

        $data = [
            "to" => $firebaseToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "job_id" => $job_id,
                "notification_type" => $type,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return $result;

    }
}

