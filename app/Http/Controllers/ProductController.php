<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class ProductController extends Controller
{

    private function getAuth()
    {
        $user = auth()->user();
        if (!is_null($user))
            return $user->type == 'u' ? $user : null;
        return null;
    }

    public function index()
    {
        $products = Product::all();
        if(empty($products)){
            return JsonResponse::success(
                $message = 'No Products Yet',
            );
        }
        return JsonResponse::success(
            $message = 'Found',
            $data = $products,
        );
    }

    public function show($id)
    {
//        $user = $this->getAuth();
//        if (is_null($user)) {
//           return JsonResponse::unauthorized();
//        }

        $data = Product::find($id);
        if(is_null($data)){
            return JsonResponse::notFound();
        }
        return JsonResponse::success(
            $message = 'Found',
            $data,
        );

    }

//    public function validation($request){
//        return Validator::make($request->all(),
//            [
//                'name'=>'required|string|max:15',
//                'category' =>'required|string|max:25',
//                'price'=>'required|numeric'
//            ]);
//    }

    public function store(ProductRequest $request)
    {

        $user = $this->getAuth();
        if (is_null($user)) {
            return JsonResponse::unauthorized();
        }
//        $validator = $this->validation($request); //there is no need for these because using FormRequest
//        if ($validator->fails()) {
//            return JsonResponse::validationError($validator->errors());
//        }
        $product = Product::create(array_merge(
            $request->all(),
            ['user_id' => $user->id]
        ));
        return JsonResponse::success(
            $message = 'Created Successfully',
            $data = $product,
        );
    }

    public function update(ProductRequest $request, $id)
    {
        $user = $this->getAuth();
        if (is_null($user)) {
            return JsonResponse::unauthorized();
        }
        $data = Product::find($id);
        if($data == null){
            return JsonResponse::notFound();
        }

//        if ($user->id != $data->user_id) {
//            return JsonResponse::unauthorized();
//        }
        if (! Gate::allows('update-product', [$user,$data])) {
            return JsonResponse::unauthorized();
        }
//        $validator = $this->validation($request);
//        if ($validator->fails()) {
//            return JsonResponse::validationError($validator->errors());
//        }

        $data->update(array_merge(
            $request->all(),
            ['user_id' => $user->id]
        ));
        return JsonResponse::success(
            $message = 'Updated Successfully',
            $data,
        );
    }

    public function destroy($id)
    {
        $user = $this->getAuth();
        if (is_null($user)) {
            return JsonResponse::unauthorized();
        }

        $data = Product::find($id);
        if(is_null($data)){
            return JsonResponse::notFound();
        }

        if ($user->id != $data->user_id) {
            return JsonResponse::unauthorized();
        }

        Product::destroy($id);
        return JsonResponse::success(
            $message = 'Deleted Successfully',
        );
    }


}
