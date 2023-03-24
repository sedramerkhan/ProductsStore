<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use function League\Flysystem\delete;
use function Nette\Utils\flatten;


class ProductController extends Controller
{

    public function index()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $product['images'] = $this->getImages($product->id);
        }

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

    private function getImages($id)
    {
        $images = Image::where('product_id', $id)->select('image')->get();

        $images2 = [];
        foreach ($images as $img) {
            array_push($images2, "storage/images/products/".$img->image);
        }
        return $images2;
    }

    private function storeImages($files, $id)
    {

        //php artisan storage:link
        for ($i=0;$i<count($files);$i++) {
            $dest_path = 'public/images/products';
            $image_name = time() . $i.'.image.png';
            $files[$i]->storeAs($dest_path, $image_name);

             Image::create([
                    'image' => $image_name,
                    'product_id' => $id
                ]
            );
        }
    }

    public function show($id)
    {
        $data = Product::find($id);
        $data['images'] = $this->getImages($id);
        if (is_null($data)) {
            return JsonResponse::notFound();
        }
        return JsonResponse::success(
            $message = 'Found',
            $data,
        );

    }

    private function getAuth()
    {
        $user = auth()->user();
        if (!is_null($user))
            return $user->type == 'u' ? $user : null;
        return null;
    }

    function validator(Request $request)
    {
        return Validator::make($request->all(),
            [
                'name' => 'required|string|max:15',
                'category' => 'required|string|max:25',
                'price' => 'required|numeric',
                'discount' => 'numeric',
                'images' => 'required',
                'images.*'=> 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);
    }

    public function store(Request $request)
    {

        $user = $this->getAuth();
        if (is_null($user)) {
            return JsonResponse::unauthorized();
        }

        $validator = $this->validator($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }

        try {

            $product = Product::create(array_merge(
                $request->except('images'),
                ['user_id' => $user->id]
            ));

            $this->storeImages($request->file('images'), $product->id);

            $product['images'] = $this->getImages($product->id);
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
        $product = Product::find($id);
        if (is_null($product)) {
            return JsonResponse::notFound();
        }

        $validator = $this->validator($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }

        if ($user->id != $product->user_id) {
            return JsonResponse::unauthorized();
        }
//        if (!Gate::allows('update-product', [$product])) {
//            return JsonResponse::unauthorized();
//        } //todo

        try {

        $product->update(array_merge(
            $request->except('images'),
            ['user_id' => $user->id]
        ));


        $deletedImages = Image::where('product_id', $id)->get();

//        return $deletedImages;
        foreach ($deletedImages as $img) {
            Image::destroy($img->id);
            Storage::delete("public/images/products/".$img->image);
        }


        $this->storeImages($request->file('images'), $product->id);

        $product['images'] = $this->getImages($product->id);

        } catch (\Exception $e) {
            return JsonResponse::failure('you cannot insert the Service', 400);
        }
        return JsonResponse::success(
            $message = 'Updated Successfully',
            $product,
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
