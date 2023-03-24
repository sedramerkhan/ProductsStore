<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
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
        if (empty($products)) {
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

        $data = Product::find($id);
        if (is_null($data)) {
            return JsonResponse::notFound();
        }
        return JsonResponse::success(
            $message = 'Found',
            $data,
        );

    }

    public function store(ProductRequest $request)
    {

        $user = $this->getAuth();
        if (is_null($user)) {
            return JsonResponse::unauthorized();
        }

        if (!$request->hasFile('images'))
            return JsonResponse::failure('there is no images', 400);

//        return $request->file('images');
//        $product = "";
        try {

            $product = Product::create(array_merge(
                $request->except('images'),
                ['user_id' => $user->id]
            ));

            $images = [];
            //php artisan storage:link
            foreach ($request->file('images') as $file) {
                $dest_path = 'public/images/products';
                $image_name = time() . '.image.png';
                $file->storeAs($dest_path, $image_name);

                $image = Image::create([
                        'image' => "storage/images/products/" . $image_name,
                        'product_id' => $product->id
                    ]
                );
                array_push($images, $image->image);
            }

            $product['images'] = $images;
        } catch (\Exception $e) {
            return JsonResponse::failure('you cannot insert the Service', 400);
        }

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
        if ($data == null) {
            return JsonResponse::notFound();
        }

//        if ($user->id != $data->user_id) {
//            return JsonResponse::unauthorized();
//        }
        if (!Gate::allows('update-product', [$user, $data])) {
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
        if (is_null($data)) {
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
