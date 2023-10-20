<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $state_id
 * @property mixed $job_type_id
 * @property mixed $event_name
 * @property mixed $street1
 * @property mixed $street2
 * @property mixed $city
 * @property mixed $zipcode
 * @property mixed $event_start
 * @property mixed $event_end
 * @property mixed $osha_license_id
 * @property mixed $job_description
 * @property mixed $price
 * @property mixed $max_price
 * @property mixed $user_id
 * @property int|mixed $price_paid
 * @property int|mixed $job_status
 * @property mixed|string|null $chat_sid
 * @property mixed|string|null $chat_service_sid
 */
class SecurityJob extends Model
{
    use HasFactory;
    protected $with = ["state","users","job_type"];
    public function state(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(State::class, "state_id");
    }
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function job_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobType::class, "job_type_id");
    }

    public function security_jobs(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JobDetail::class, 'job_id','id');
    }
}
