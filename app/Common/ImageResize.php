<?php

namespace App\Common;

class ImageResize
{
    public static function sizeFormat($type): string
    {
        switch($type) {
            case(1):
                return 150;
                break;
            case(2):
                return 350;
                break;
            case(3):
                return 500;
                break;
            case(4):
                return 650;
                break;
            default:
                return 550;
        }
    }
}
