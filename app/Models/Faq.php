<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $question
 * @property mixed $answer
 */
class Faq extends Model
{
    use HasFactory;

    protected $hidden = [
        "id",
        "created_at",
        "updated_at",
        "active"
    ];
}
