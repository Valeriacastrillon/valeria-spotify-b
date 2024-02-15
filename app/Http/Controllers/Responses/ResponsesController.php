<?php

namespace App\Http\Controllers\Responses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponsesController extends Controller
{
    public static function success($data = [], $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'statusCode' => $statusCode,
        ], $statusCode);
    }

    public static function error($data = [], $message = 'Error', $statusCode = 400)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'statusCode' => $statusCode,
        ], $statusCode);
    }
}