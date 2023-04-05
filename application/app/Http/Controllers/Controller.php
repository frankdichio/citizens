<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

        /**
     * Method to send response
     *
     * @param array $payload
     * @param string $message
     * @param int $statusCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendResponse($message, $payload = null, $statusCode = 200) 
    {
        $response = [
            'success' => true,
            'data' => $payload,
            'message' => $message
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Method to send response error
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $statusCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendError($error, $errorMessages = [], $statusCode = 405)
    {
        $response = [
            'success' => false,
            'message' => $error
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $statusCode);
    }
}
