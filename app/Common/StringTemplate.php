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
                return "You have received a clock-in request for " . $job . " Click the link " . Config::get('constants.web_url') . "job-detail/" . $job_id;
                break;
            case(3):
                return "You have received a clock-out request for " . $job . " Click the link " . Config::get('constants.web_url') . "job-detail/" . $job_id;
                break;
            case(4):
                return "Your account has been approved you can login now.";
                break;
            case(5):
                return "Your account has been denied you can login now to change the following:\n " . $job;
                break;
            default:
                return 'Something went wrong.';
        }

    }
    public static function notifications($type): string
    {
        switch($type) {
            case(1):
                return "Job selected";
                break;
            case(2):
                return "Job cancelled";
                break;
            case(3):
                return "Clock-in request";
                break;
            case(4):
                return "Clock-out request";
                break;
            case(5):
                return "Request additional time";
                break;
            case(6):
                return "Additional time accepted";
                break;
            case(7):
                return "Someone is chatting";
                break;
            default:
                return 'Something went wrong.';
        }
    }
    public static function response($message): string
    {
        switch ($message) {
            case (1):
                return "The job is within 4 hours of the start/end time of another job.";
                break;
            case (2):
                return "You have an expired license in your profile. Please update your profile to replace the expired license.";
                break;
            default:
                return 'Something went wrong.';
        }
    }
}
