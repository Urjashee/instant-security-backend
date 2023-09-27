<?php
namespace App\Common;
use \stdClass;

class ResponseFormatter {
 
	public static function successResponse($message = 'Operation completed successfully.', $data = ''){
		if($data == ''){
			//$oVal = json_decode('{}');
			//$oVal = response()->json(new stdClass());
			return response()->json(array('status' => 'OK', 'message' => $message));
		}
		return response()->json(array('status' => 'OK', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)));
	}
	
	public static function errorResponse($message = 'Failed to complete the operation!', $data = ''){
		
		if($data == ''){
			//$oVal = json_decode('{}');
			//$oVal = new stdClass();
			//$oVal = response()->json();
			return response()->json(array('status' => 'ERROR', 'message' => $message));
		}
		return response()->json(array('status' => 'ERROR', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)));
	}
	
	public static function unauthorizedResponse($message = 'You are not authorized to access this resource!', $data = ''){
		if($data == ''){
			//$oVal = json_decode('{}');
			//$oVal = new stdClass();
			return response()->json(array('status' => 'UNAUTHORIZED', 'message' => $message));
		}
		return response()->json(array('status' => 'UNAUTHORIZED', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)));
	}
	
	public static function forbiddenResponse($message = 'You are not allowed to access this resource!', $data = ''){
		if($data == ''){
			//$oVal = json_decode('{}');
			//$oVal = new stdClass();
			return response()->json(array('status' => 'FORBIDDEN', 'message' => $message));
		}
		return response()->json(array('status' => 'FORBIDDEN', 'message' => $message, 'data' => ResponseFormatter::createDataKeyValueIfRequired($data)));
	}
	
	/**
	 * 
	 * @param array $objects
	 * @param string $fileName tasks.csv
	 * @param string $fileType text/csv
	 */
	public static function downloadCSVResponse($csvRows, $fileName) {
		$headers = array(
				"Content-Type"        => 'text/csv; charset=utf-8',
				"Content-Disposition" => "attachment; filename=$fileName",
				"Pragma"              => "no-cache",
				"Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
				"Expires"             => "0"
		);
		
		$callback = function() use($csvRows) {
			$file = fopen('php://output', 'w');
		
			foreach ($csvRows as $row) {
				fputcsv($file, $row);
			}
		
			fclose($file);
		};
		
		return response()->stream($callback, 200, $headers);
	}
	
	private static function createDataKeyValueIfRequired($data) {
		if(is_array($data) || is_object($data)) {
			return $data;
		}
		return !empty($data) ? array('value' => $data) : $data;
	}
	
	public static function pageSize() {
		return 10;
	}

}

?>