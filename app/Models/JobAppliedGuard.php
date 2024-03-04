<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $guard_id
 */
class JobAppliedGuard extends Model
{
    protected $table = 'job_applied_guards';
    use HasFactory;
}
