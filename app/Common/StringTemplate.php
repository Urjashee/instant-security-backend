<?php

namespace App\Common;

class StringTemplate
{
    public static function typeMessage($type,$instrument,$name,$jam_creator,$jam_name): string
    {
        switch($type) {
            case(1):
                return $jam_creator . " is inviting you to join their Jam Session";
                break;
            case(2):
                return $name . " accepted the invitation to join your Jam Session!";
                break;
            case(3):
                return $name . " denied the request to join your JAM Session. You can search again to fill the open position.";
                break;
            case(4):
                return $name . " did not respond in time to the request to join your JAM Session. You can search again to fill the open position.";
                break;
            case(5):
                return $jam_creator . " is inviting you to join the group chat for" . $jam_name;
                break;
            case(6):
                return "All positions have been filled for" . $jam_name . " You can now connect with your group members in the JAM Session's group chat.";
                break;
            default:
                return 'Something went wrong.';
        }

    }
    public static function typeHeading($type): string
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
