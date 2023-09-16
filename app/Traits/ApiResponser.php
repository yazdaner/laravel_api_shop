<?php

namespace App\Traits;

trait ApiResponser{
    protected function successResponse($data, $statusCode = 200, $message = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ],$statusCode);
    }

    protected function errorResponse($message = null, $statusCode)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => '',
        ],$statusCode);
    }
}
