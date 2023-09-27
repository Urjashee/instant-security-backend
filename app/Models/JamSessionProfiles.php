<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $jam_session_id
 * @property mixed $instrument_id
 * @property mixed $profile_id
 */
class JamSessionProfiles extends Model
{
    use HasFactory;
    protected $with = ["jam","user"];
    public function jam()
    {
        return $this->belongsTo(JamSessions::class, "jam_session_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "profile_id");
    }
}
