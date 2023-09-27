<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $user_id
 * @property mixed $latitude
 * @property mixed $longitude
 * @property mixed $location
 */
class CurrentLocations extends Model
{
    use HasFactory;
    use SpatialTrait;
    protected $fillable = [
        'location',
    ];

    protected $spatialFields = [
        'location',
    ];
    public function scopeDistance($query, $latitude, $longitude, $distance, $unit = "km")
    {
        $constant = $unit == "km" ? 6371 : 3959;
        $haversine = "(
        $constant * acos(
            cos(radians(" .$latitude. "))
            * cos(radians(`latitude`))
            * cos(radians(`longitude`) - radians(" .$longitude. "))
            + sin(radians(" .$latitude. ")) * sin(radians(`latitude`))
        )
    )";

        return $query->selectRaw("$haversine AS distance")
            ->having("distance", "<=", $distance);
    }
}
