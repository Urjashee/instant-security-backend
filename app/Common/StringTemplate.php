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
            case(6):
                return "Your job request for " . $job . " has been accepted";
                break;
            case(7):
                return "Your request for " . $job . " has been accepted";
                break;
            case(8):
                return "Your job request for " . $job . " has been rejected";
                break;
            case(9):
                return "Your request for " . $job . " has been rejected";
                break;
            case(10):
                return "Extra time request for  " . $job;
                break;
            case(11):
                return "Extra time request for  " . $job . " has been accepted";
                break;
            case(12):
                return "Extra time request for  " . $job . " has been rejected";
                break;
            default:
                return 'Something went wrong.';
        }

    }

    public static function notifications($type): string
    {
        switch($type) {
            case(1):
                return "Job assigned";
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
                return "Additional time rejected";
                break;
            case(8):
                return "Someone is chatting";
                break;
            case(9):
                return "Job has been accepted";
                break;
            case(10):
                return "Job has been rejected";
                break;
            default:
                return 'Something went wrong.';
        }
    }
    public static function notificationsTitle($type,$job): string
    {
        switch($type) {
            case(1):
                return "Job assigned for " .$job;
                break;
            case(2):
                return "Job cancelled for " . $job;
                break;
            case(3):
                return "Clock-in request for the job ". $job;
                break;
            case(4):
                return "Clock-out request for the job ". $job;
                break;
            case(5):
                return "Request additional time for the job ". $job;
                break;
            case(6):
                return "Additional time accepted for the job ". $job;
                break;
            case(7):
                return "Additional time rejected for the job ". $job;
                break;
            case(8):
                return "Someone is chatting on the job ". $job;
                break;
            default:
                return 'Something went wrong.';
        }
    }
    public static function notificationsMessage($type,$user): string
    {
        switch($type) {
            case(1):
                return $user . " was assigned the job";
                break;
            case(2):
                return $user . " cancelled the job";
                break;
            case(3):
                return $user . " sent you a request for clock-in time.";
                break;
            case(4):
                return $user . " sent you a request for clock-out time.";
                break;
            case(5):
                return "Request additional time";
                break;
            case(6):
                return $user . " accepted the additional time";
                break;
            case(7):
                return $user . " rejected the additional time";
                break;
            case(8):
                return "Someone is chatting on the job";
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
            case (3):
                return "Banking details missing. Please update your profile.";
                break;
            case (4):
                return "You have already applied for the job.";
                break;
            case (5):
                return "The job is within 4 hours of the start/end time of another job for the user.";
                break;
            default:
                return 'Something went wrong.';
        }
    }
}
