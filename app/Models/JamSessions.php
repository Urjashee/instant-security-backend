<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $jam_name
 * @property mixed $genres
 * @property mixed $instruments
 * @property int|mixed $chat_status
 * @property mixed|string $chat_sid
 * @property mixed $chat_service_sid
 * @property mixed $chat_friendly_name
 */
class JamSessions extends Model
{
    protected $table = "jam_session";
    protected $with = ["user"];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
