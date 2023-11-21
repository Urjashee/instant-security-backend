<?php
namespace App\Common;

class ResponseFormatter {

	public static function successResponse($message = 'Operation completed successfully.', $data = ''): \Illuminate\Http\JsonResponse
    {
		if($data == ''){
			return response()->json(array('status' => 'OK', 'message' => $message), 200);
		}
		return response()->json(array('status' => 'OK', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)), 200);
	}

	public static function errorResponse($message = 'Failed to complete the operation!', $data = ''): \Illuminate\Http\JsonResponse
    {
		if($data == ''){
			return response()->json(array('status' => 'ERROR', 'message' => $message), 400);
		}
		return response()->json(array('status' => 'ERROR', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)), 400);
	}

	public static function unauthorizedResponse($message = 'You are not authorized to access this resource!', $data = ''): \Illuminate\Http\JsonResponse
    {
		if($data == ''){
			return response()->json(array('status' => 'UNAUTHORIZED', 'message' => $message), 401);
		}
		return response()->json(array('status' => 'UNAUTHORIZED', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)), 401);
	}

	public static function forbiddenResponse($message = 'You are not allowed to access this resource!', $data = ''): \Illuminate\Http\JsonResponse
    {
		if($data == ''){
			return response()->json(array('status' => 'FORBIDDEN', 'message' => $message), 403);
		}
		return response()->json(array('status' => 'FORBIDDEN', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)), 403);
	}

	private static function createDataKeyValueIfRequired($data) {
		if(is_array($data) || is_object($data)) {
			return $data;
		}
		return !empty($data) ? array('value' => $data) : $data;
	}

}

?>
