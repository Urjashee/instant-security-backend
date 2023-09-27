<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $instrument_id
 * @property mixed $experience
 * @property mixed $level
 */
class ProfileInstruments extends Model
{
    use HasFactory;
    protected $with = ["instrument_level","instruments"];
    public function instrument_level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Level::class, "level");
    }
    public function instruments(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Instruments::class, "instrument_id");
    }
}
