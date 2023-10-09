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
 */
class SecurityJob extends Model
{
    use HasFactory;
}
