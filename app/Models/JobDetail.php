<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $guard_id
 * @property mixed|string|null $participant_id
 * @property mixed $chat_sid
 */
class JobDetail extends Model
{
    use HasFactory;
    protected $with = ["users","jobs"];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, "guard_id");
    }
    public function jobs(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SecurityJob::class, "job_id");
    }
    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'user_id','guard_id');
    }
}
