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

    public static function postType($type): string
    {
        switch($type) {
            case(1):
                return "Public";
                break;
            case(2):
                return "Friends-Only";
                break;
            case(3):
                return "Anonymous";
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
}
