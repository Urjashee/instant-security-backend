<?php

namespace App\Http\Controllers;

use App\Common\ConfigList;
use App\Common\ResponseFormatter;
use App\Models\Amenities;
use App\Models\Genre;
use App\Models\GenreCategories;
use App\Models\Instruments;
use App\Models\Level;
use App\Models\ProfileTime;
use Illuminate\Http\Request;

class ListController extends Controller
{
    //    Get Config List
    public function getAllLists(): \Illuminate\Http\JsonResponse
    {
        $amenitiesList = array();
        $amenities = Amenities::all();
        if ($amenities) {
            foreach ($amenities as $amenity) {
                $amenitiesList[] = [
                    'amenity_id' => $amenity->id,
                    'amenity_name' => $amenity->name,
                ];
            }
            $allList[] = [
                'minimum_radius' => ConfigList::defaultValues(1),
                'maximum_radius' => ConfigList::defaultValues(2),
                'minimum_fee' => ConfigList::defaultValues(3),
                'maximum_fee' => ConfigList::defaultValues(4),
                'amenities' => $amenitiesList,
            ];
        }
        return ResponseFormatter::successResponse("", $allList);
    }
}
