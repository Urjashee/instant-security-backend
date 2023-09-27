<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $jam_creator_id
 * @property mixed $user_id
 * @property mixed $jam_id
 * @property mixed $instrument_id
 * @property mixed $type
 * @property mixed $notification_user_id
 */
class Notifications extends Model
{
    use HasFactory;
    protected $with = ["user","jam","instrument","creator"];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function jam()
    {
        return $this->belongsTo(JamSessions::class, "jam_id");
    }
    public function instrument()
    {
        return $this->belongsTo(Instruments::class, "instrument_id");
    }
    public function creator()
    {
        return $this->belongsTo(User::class, "jam_creator_id");
    }
}
