<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $user_id
 * @property mixed $message
 * @property mixed $timestamp
 * @property mixed $image
 */
class ActivityReport extends Model
{
    use HasFactory;
}
