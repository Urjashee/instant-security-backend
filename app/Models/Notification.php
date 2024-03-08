<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $user_id
 * @property mixed $notification_user_id
 * @property mixed $type
 */
class Notification extends Model
{
    use HasFactory;
    protected $with = ["user", "user_profile_data", "jobs"];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function user_profile_data()
    {
        return $this->belongsTo(UserProfile::class, "user_id");
    }
    public function jobs()
    {
        return $this->belongsTo(SecurityJob::class, "job_id");
    }
}
