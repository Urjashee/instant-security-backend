<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $state_id
 * @property mixed|string $security_guard_license_image
 * @property mixed $security_guard_license_expiry
 * @property mixed $fire_guard_license_type
 * @property mixed|string $fire_guard_license_image
 * @property mixed $fire_guard_license_expiry
 * @property mixed|string $cpr_certificate_image
 * @property mixed $cpr_certificate_expiry
 * @property mixed $user_id
 */
class StateLicense extends Model
{
    use HasFactory;
    protected $table = 'state_licenses';
    protected $with = ["state","fire_arms"];
    public function state()
    {
        return $this->belongsTo(States::class, "state_id");
    }
    public function fire_arms()
    {
        return $this->belongsTo(Firearms::class, "fire_guard_license_type");
    }
}
