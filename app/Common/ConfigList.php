<?php
namespace App\Common;

class ConfigList {

    public static function dayString($type): string
    {
        switch($type) {
            case(0):
                return "Monday";
                break;
            case(1):
                return "Tuesday";
                break;
            case(2):
                return "Wednesday";
                break;
            case(3):
                return "Thursday";
                break;
            case(4):
                return "Friday";
                break;
            case(5):
                return "Saturday";
                break;
            case(6):
                return "Sunday";
                break;
            default:
                return 'Something went wrong.';
        }
    }

    public static function oshaType($type): string
    {
        switch($type) {
            case(1):
                return "OSHA 10";
                break;
            case(2):
                return "OSHA 30";
                break;
            default:
                return 'Something went wrong.';
        }
    }

    public static function jobType($type): string
    {
        switch($type) {
            case(0):
                return "Open Job";
                break;
            case(1):
                return "Upcoming Job";
                break;
            case(2):
                return "Completed Job";
                break;
            case(3):
                return "Cancelled Job";
                break;
            case(4):
                return "Ongoing Job";
                break;
            case(5):
                return "Expired Job";
                break;
            case(6):
                return "Pending Review";
                break;
            case(7):
                return "Rejected";
                break;
            case(8):
                return "Pending Assignment";
                break;
            case(9):
                return "Assigned";
                break;
            case(10):
                return "Pending Application";
                break;
            default:
                return 'Something went wrong.';
        }
    }

    public static function dropInType($type): string
    {
        switch($type) {
            case(1):
                return "Daily";
                break;
            case(2):
                return "Weekly";
                break;
            default:
                return 'Something went wrong.';
        }
    }

    public static function defaultValues($value) {
        switch($value) {
            case(1):
                return 1;//Minimum Radius
                break;
            case(2):
                return 100;//Maximum Radius
                break;
            case(3):
                return 0;//Minimum Experience
                break;
            case(4):
                return 100;//Maximum Experience
                break;
            default:
                return 'Something went wrong.';
        }
    }
    public static function notificationType($type): string
    {
        switch($type) {
            case(1):
                return "Assign Job";
                break;
            case(2):
                return "Cancel Job";
                break;
            case(3):
                return "Clock-in Request";
                break;
            case(4):
                return "Clock-out Request";
                break;
            case(5):
                return "Extra time request";
                break;
            case(6):
                return "Extra time Accepted";
                break;
            case(7):
                return "Extra time Rejected";
                break;
            case(8):
                return "Chat";
                break;
            case(9):
                return "Job Accepted";
                break;
            case(10):
                return "Job Rejected";
                break;
            default:
                return 'Something went wrong.';
        }
    }
}
