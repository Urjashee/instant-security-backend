<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $email
 * @property mixed|string $token
 * @property Carbon|mixed $created_at
 * @property int|mixed $type
 * @property mixed|string $active_token
 */
class PasswordReset extends Model
{
    use HasFactory;
}
