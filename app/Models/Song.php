<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $song_name
 * @property mixed $song_image
 * @property mixed $song_id
 * @property mixed $album_name
 */
class Song extends Model
{
    use HasFactory;
}
