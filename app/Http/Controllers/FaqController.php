<?php

namespace App\Http\Controllers;

use App\Common\ResponseFormatter;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function addFaqs(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "question" => "required",
            "answer" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $faqs = new Faq();
        $faqs->question = $request->input("question");
        $faqs->answer = $request->input("answer");
        $faqs->save();

        return ResponseFormatter::successResponse("Faq added successfully");
    }

    public function getFaqs(Request $request): \Illuminate\Http\JsonResponse
    {
        $faqs = Faq::where("active", 1)->get();
        return ResponseFormatter::successResponse("Faqs", $faqs);
    }

    public function updateFaqs(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $faq = Faq::where("id",$id)->first();
        if ($faq) {
            $faq->answer = $request->input("answer");
            $faq->question = $request->input("question");
            if ($request->has("active")) {
                $faq->active = $request->input("active");
            }
            $faq->update();
            return ResponseFormatter::successResponse("Faq updated");
        } else {
            return ResponseFormatter::errorResponse("Faq cannot be updated");
        }
    }

}
