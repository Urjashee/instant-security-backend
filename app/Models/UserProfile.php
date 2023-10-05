<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $address1
 * @property mixed $address2
 * @property mixed $state
 * @property mixed $city
 * @property mixed $zipcode
 */
class UserProfile extends Model
{
    use HasFactory;
    protected $with = ["user"];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
