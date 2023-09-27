<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $jam_id
 * @property mixed $instrument_id
 */
class RejectedUsers extends Model
{
    use HasFactory;
}
