<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $state_id
 * @property mixed $fire_guard_license_type
 * @property mixed|string $fire_guard_license_image
 * @property mixed $fire_guard_license_expiry
 */
class FireGuardLicense extends Model
{
    use HasFactory;
    protected $with = ["state","fire_arms"];
    public function state()
    {
        return $this->belongsTo(State::class, "state_id");
    }
    public function fire_arms()
    {
        return $this->belongsTo(Firearms::class, "fire_guard_license_type");
    }
}
