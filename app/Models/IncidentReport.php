<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $name
 * @property mixed $message
 * @property mixed $image
 * @property mixed $job_id
 * @property mixed $user_id
 */
class IncidentReport extends Model
{
    use HasFactory;
}
