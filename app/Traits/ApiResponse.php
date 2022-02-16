<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Laravel\Lumen\Http\ResponseFactory;

trait ApiResponse
{

    /**
     * Success Response
     * @param array $data
     * @param boolean $status
     * @param int $code
     * @return JsonResponse
     */
    public function successResponse($data, $message, $status = true, $code = Response::HTTP_OK)
    {
            return response()->json(
                        [
                            'code' => $code,
                            'status' => $status,
                            'message' => $message,
                            'data' => $data
                        ],
                        $code
                    )->header('Content-Type', 'application/json');
    }

 
    /**
     * Error Response With Error Details
     * @param string $message
     * @param array | MessageBag $errors
     * @param int $code
     * @param boolean $status
     * @return JsonResponse
     */
    public function errorResponseWithDetails($message, $errors, $code, $status = false)
    {
        return response()->json(
                            [
                                'code' => $code,
                                'status' => $status,
                                'message' => $message,
                                'errors' => $errors,
                                // 'errors' => array_values(collect($errors)->all())
                            ],
                            $code
                        );
    }

    /**
     * Error Response Without Error Details
     * @param string $message
     * @param int $code
     * @param boolean $status
     * @return JsonResponse
     */
    public function errorResponseWithoutDetails($message, $code, $status = false)
    {
         return response()->json(
                            [
                                'code' => $code,
                                'status' => $status,
                                'message' => $message
                            ],
                            $code
                        );
    }

    /**
     * Error Message
     * @param string $message
     * @param int $code
     * @return json|Response|ResponseFactory
     */
    public function errorMessage($message, $code)
    {
        return response($message, $code)->header('Content-Type', 'application/json');
    }
}
