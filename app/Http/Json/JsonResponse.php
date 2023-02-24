<?php

namespace App\Http\Json;

class JsonResponse
{

    public static function success($message='',$data = null,$token = null){
        $jsonArray = [
            'status' => true,
            'message' => $message,
            ];
        if($data != null)
            $jsonArray['data'] = $data;
        if($token != null)
            $jsonArray['token'] = $token;
        return response()->json($jsonArray, 200);
    }

    public static function failure($message='',$code=null,$errors=null,$status= false){
        $jsonArray = [
            'status' => $status,
            'message' => $message,
        ];
        if($errors != null)
            $jsonArray['errors']  =$errors;
        return response()->json($jsonArray, $code);
    }

    public static function unauthorized(){
         return JsonResponse::failure(
            $message = 'Unauthorised',
            $code = '401'
        );
    }

    public static function validationError($validationErrors){
        return JsonResponse::failure(
            $massage = 'Validation Error',
            $code = 422,
            $errors= $validationErrors);
    }

    public static function notFound(){
        return JsonResponse::failure(
            $massage = 'Not Found',
            $code = 404,
            );
    }
}
