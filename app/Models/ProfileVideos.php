<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $video_url
 * @property mixed|string $thumbnail_url
 */
class ProfileVideos extends Model
{
    use HasFactory;
}
