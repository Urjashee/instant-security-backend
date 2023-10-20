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
        $jobList = array();
        $states = State::where("active",1)->get();
        $fireArms = Firearms::all();
        if ($states) {
            foreach ($states as $state) {
                $stateList[] = [
                    'id' => $state->id,
                    'name' => $state->name,
                    'fire_guard_license' => $state->fire_guard_license == 1 ? true : false,
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
        for ($days = 0; $days <= 6; $days++) {
            $dayList[] = [
                'id' => $days,
                'name' => ConfigList::dayString($days),
            ];
        }
        for ($jobs = 1; $jobs <= 4; $jobs++) {
            $jobList[] = [
                'id' => $jobs,
                'name' => ConfigList::jobType($jobs),
            ];
        }
        $allList = [
            'states' => $stateList,
            'fire_arms' => $fireArmsList,
            'osha' => $oshaList,
            'day_of_week' => $dayList,
            'job_type' => $jobList,
        ];
        return ResponseFormatter::successResponse("", $allList);
    }
}
