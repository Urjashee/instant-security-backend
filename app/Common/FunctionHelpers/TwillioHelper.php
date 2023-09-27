<?php
namespace App\Common\FunctionHelpers;

use Illuminate\Support\Facades\Config;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Rest\Client;

class TwillioHelper
{

    public static function genrateTokenForIdentity($identity,$serviceId)
    {
        // Required for all Twilio access tokens
        $twilioAccountSid = Config::get('constants.twilio_access');
        $twilioApiKey = Config::get('constants.twilio_api_key');
        $twilioApiSecret = Config::get('constants.twilio_secret_key');

        $serviceSid = $serviceId;;

        // Create access token, which we will serialize and send to the client
        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            3600,
            $identity
        );

        // Create Chat grant
        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid($serviceSid);

        // Add grant to token
        $token->addGrant($chatGrant);
        // render token to string
        return $token->toJWT();
    }

    public static function createConversation($frendlyName)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $conversation = $twilio->conversations->v1->conversations
            ->create([
                    "friendlyName" => $frendlyName
                ]
            );

        return $conversation;
    }

    public static function getConversationWithSid($sid)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $conversation = $twilio->conversations->v1->conversations($sid)
            ->fetch();

        return $conversation;
    }

    public static function deleteConversationWithSid($chat_id)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $twilio->conversations->v1->conversations($chat_id)
            ->delete();
    }

    public static function addChatParticipantToConversation($identity, $conversationSid)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $participant = $twilio->conversations->v1->conversations($conversationSid)
            ->participants
            ->create([
                    "identity" => $identity
                ]
            );

        return $participant->sid;
    }

    public static function getChatParticipantDetails($conversationSid, $participantSid)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $participant = $twilio->conversations->v1->conversations($conversationSid)
            ->participants($participantSid)
            ->fetch();

        return $participant;
    }

    public static function deleteChatParticipantFromConversation($conversationSid, $participantSid)
    {
        $sid = Config::get('constants.twilio_access');
        $token = Config::get('constants.twilio_secret');
        $twilio = new Client($sid, $token);

        $deleteUser = $twilio->conversations->v1->conversations($conversationSid)
            ->participants($participantSid)
            ->delete();

        return $deleteUser;
    }
}

?>
