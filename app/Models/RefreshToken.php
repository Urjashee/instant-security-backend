<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed|string $uuid
 * @property mixed|string $hash
 */
class RefreshToken extends Model
{
    use HasFactory;
}
