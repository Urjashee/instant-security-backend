<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $day
 * @property mixed $from_time
 * @property mixed $to_time
 */
class ProfileTime extends Model
{
    use HasFactory;
}
