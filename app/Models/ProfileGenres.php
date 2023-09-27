<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $genre_id
 */
class ProfileGenres extends Model
{
    use HasFactory;
    protected $with = ["genres"];
    public function genres(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Genre::class, "genre_id");
    }
}
