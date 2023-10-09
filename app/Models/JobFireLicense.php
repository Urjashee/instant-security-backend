<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $job_id
 * @property mixed $fire_guard_license_id
 */
class JobFireLicense extends Model
{
    use HasFactory;
    protected $table = "job_fire_license";
}
