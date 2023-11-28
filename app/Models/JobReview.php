<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $user_id
 * @property mixed $rating
 * @property mixed $message
 */
class JobReview extends Model
{
    use HasFactory;
}
