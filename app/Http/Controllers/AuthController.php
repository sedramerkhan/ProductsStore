<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery\Exception;

class AuthController extends Controller
{

    function sign_up_validator(Request $request, $users)
    {
        //Validated
        return Validator::make($request->all(),
            [
                'name' => 'required',
                'phone_number' => "required|string|unique:{$users}",
                'password' => 'required|confirmed',
                'type' => 'required|string|max:1'
            ]);
    }

    public function sign_up(Request $request)
    {
        try {
            $validator = $this->sign_up_validator($request,'users');
            if ($validator->fails()) {
                return JsonResponse::validationError($validator->errors());
            }

            $user = User::create(array_merge(
                $request->except('password'),
                ['password' => Hash::make($request->password)]));

            return JsonResponse::success('User is Created Successfully',
                null,
                $user->createToken("API TOKEN")->plainTextToken);


        } catch (Exception $th) {
            return JsonResponse::failure($message = $th->getMessage(), $code = 500);
        }
    }


    function sign_in_validator(Request $request)
    {
        return Validator::make($request->all(),
            [
                'phone_number' => 'required|string',
                'password' => 'required'
            ]);
    }

    public function sign_in(Request $request)
    {
        try {
            $validator = $this->sign_in_validator($request);

            if ($validator->fails()) {
                return JsonResponse::validationError($validator->errors());
            }
            //Way1
            $user = User::where('phone_number', $request->phone_number)->first();

            if (Hash::check($user->password, $request->password)) {
                return JsonResponse::failure(
                    $massage = 'Phone Number & Password does not match with our record.',
                    $code = 401);
            }

            return JsonResponse::success('User Logged In Successfully',
                null,
                $user->createToken("API TOKEN")->plainTextToken);


        } catch (Exception $th) {
            return JsonResponse::failure($message = $th->getMessage(), $code = 500);
        }
    }

    public function sign_out()
    {
        auth()->user()->tokens()->delete();
        return JsonResponse::success("Sign out Successfully");
    }

    public function me(){
        return auth()->user();
    }
}
