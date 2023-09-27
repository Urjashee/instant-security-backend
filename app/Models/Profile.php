<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $location
 * @property mixed $zipcode
 * @property mixed $age
 * @property mixed $profile_image
 * @property mixed $profile_bio
 * @property mixed $user_id
 * @property mixed $friendly_name
 * @property mixed|string $extensions
 */
class Profile extends Model
{
    use HasFactory;
    protected $with = ["user"];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
