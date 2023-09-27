<?php
namespace App\Common;

class ConfigList {

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
