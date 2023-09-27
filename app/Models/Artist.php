<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $artist_name
 * @property mixed $artist_image
 * @property mixed $artist_id
 * @property mixed $genre_name
 */
class Artist extends Model
{
    use HasFactory;
}
