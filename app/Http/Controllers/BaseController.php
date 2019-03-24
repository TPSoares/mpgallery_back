<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $message){
        $response = [
            'sucess' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response,200);
    }

    public function sendError($error, $code = 400){
        $response = [
            'sucess' => false,
            'message' => $error,
        ];

        return response()->json($response,$code);
    }

}
