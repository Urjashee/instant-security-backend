<?php

namespace App\Http\Controllers;

use App\Common\ConfigList;
use App\Common\ResponseFormatter;
use App\Models\Firearms;
use App\Models\State;
use Illuminate\Http\Request;

class ListController extends Controller
{
    //    Get Config List
    public function getAllLists(): \Illuminate\Http\JsonResponse
    {
        $stateList = array();
        $fireArmsList = array();
        $oshaList = array();
        $dayList = array();
        $states = State::where("active",1)->get();
        $fireArms = Firearms::all();
        if ($states) {
            foreach ($states as $state) {
                $stateList[] = [
                    'id' => $state->id,
                    'name' => $state->name,
                ];
            }
        }
        if ($fireArms) {
            foreach ($fireArms as $fireArm) {
                $fireArmsList[] = [
                    'id' => $fireArm->id,
                    'name' => $fireArm->name,
                ];
            }
        }
        for ($osha = 1; $osha <= 2; $osha++) {
            $oshaList[] = [
                'id' => $osha,
                'name' => ConfigList::oshaType($osha),
            ];
        }
        for ($days = 1; $days <= 2; $days++) {
            $dayList[] = [
                'id' => $days,
                'name' => ConfigList::dayString($days),
            ];
        }
        $allList = [
            'states' => $stateList,
            'fire_arms' => $fireArmsList,
            'osha' => $oshaList,
            'day_of_week' => $dayList,
        ];
        return ResponseFormatter::successResponse("", $allList);
    }
}
