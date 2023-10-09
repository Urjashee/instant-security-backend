<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $state_id
 * @property mixed $name
 * @property mixed $hourly_rate
 * @property mixed $user_id
 */
class JobType extends Model
{
    use HasFactory;
}
