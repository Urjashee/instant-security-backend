<?php

namespace App\Common;

use Illuminate\Support\Facades\Config;

class StringTemplate
{
    public static function typeMessage($type,$job,$guard,$job_id): string
    {
        switch($type) {
            case(1):
                return $job . " has been cancelled by " . $guard;
                break;
            case(2):
                return "You have received a clock-in request for " . $job . "Click the link" . Config::get('constants.web_url') . "/job-detail/" . $job_id;
                break;
            case(3):
                return "You have received a clock-out request for " . $job . "Click the link" . Config::get('constants.web_url') . "/job-detail/" . $job_id;
                break;
            default:
                return 'Something went wrong.';
        }

    }
    public static function notifications($type): string
    {
        switch($type) {
            case(1):
                return "New Match Request";
                break;
            case(2):
                return "Match Request Accepted";
                break;
            case(3):
                return "Match Request Denied";
                break;
            case(4):
                return "Match Request Expired";
                break;
            case(5):
                return "Join a New Group Chat";
                break;
            case(6):
                return "New JAM Session";
                break;
            default:
                return 'Something went wrong.';
        }
    }
}
