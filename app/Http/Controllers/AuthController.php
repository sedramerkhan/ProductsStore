<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            $validator = $this->sign_up_validator($request, ($request->type == 'u') ? 'users' : 'customers');
            if ($validator->fails()) {
                return JsonResponse::validationError($validator->errors());
            }

            $data = ['name' => $request->name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password)];

            $user = ($request->type == 'u') ? [User::create($data), "User"] : [Customer::create($data), 'Customer'];

            return JsonResponse::success($user[1] . ' is Created Successfully',
                null,
                $user[0]->createToken("API TOKEN")->plainTextToken);


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
            $user = [User::where('phone_number', $request->phone_number)->first(), "User"];
            if (is_null($user[0]) or Hash::check($user[0]->password, $request->password)) {
                $user = [Customer::where('phone_number', $request->phone_number)->first(), 'Customer'];

            }

            if (is_null($user[0]) or Hash::check($user[0]->password, $request->password)) {
                return JsonResponse::failure(
                    $massage = 'Phone Number & Password does not match with our record.',
                    $code = 401);
            }

//            ///Way 2
//            if (!Auth::guard('customer2')->attempt($request->only(['phone_number', 'password']))) {
//                return JsonResponse::failure(
//                    $massage = 'Phone Number & Password does not match with our record.',
//                    $code = 401);
//            }
//            if (!Auth::attempt($request->only(['phone_number', 'password']))) {
//                return JsonResponse::failure(
//                    $massage = 'Phone Number & Password does not match with our record.',
//                    $code = 401);
//            }
//
//            $user = [User::where('phone_number', $request->phone_number)->first(),
//                "User"] ;
//            if (is_null($user[0])) {
//                $user =  [Customer::where('phone_number', $request->phone_number)->first(), 'Customer'];
//            }
            ////
            return JsonResponse::success($user[1] . ' Logged In Successfully',
                null,
                $user[0]->createToken("API TOKEN")->plainTextToken);


        } catch (Exception $th) {
            return JsonResponse::failure($message = $th->getMessage(), $code = 500);
        }
    }

    public function sign_out()
    {
//        dd(auth()->user()->id);
        auth()->user()->tokens()->delete();
//       return auth()->user();
        return "deleted Successfully";
    }
}
