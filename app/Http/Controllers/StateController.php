<?php

namespace App\Http\Controllers;

use App\Common\ResponseFormatter;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StateController extends Controller
{
    public function changeStateStatus($id): \Illuminate\Http\JsonResponse
    {
        $state = State::where("id", $id)->first();
        if ($state) {
            $state->active = 1;
            $state->update();

            return ResponseFormatter::successResponse("State successfully updated");
        } else {
            return ResponseFormatter::errorResponse("No such State");
        }
    }
}
