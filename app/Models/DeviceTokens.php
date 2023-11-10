<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $device_token
 * @property mixed $device_uuid
 * @property mixed $device_type
 * @property mixed $token
 */
class DeviceTokens extends Model
{
    use HasFactory;
}
