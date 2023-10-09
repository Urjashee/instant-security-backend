<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $email
 * @property mixed $user_role_id
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $active
 * @property mixed $password
 * @property mixed $friendly_name
 * @property mixed $address
 * @property mixed $phone_no
 * @property mixed $state_id
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $with = ["role"];
    public function role()
    {
        return $this->belongsTo(Roles::class, "user_role_id");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
