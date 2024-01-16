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
}
