<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    private function getAuth()
    {
        return auth('customer')->user();
    }

    public function index()
    {
        $user = $this->getAuth();

        $orders = is_null($user) ? Order::with('products')->get() :
            Order::with('products')->where('customer_id', $user->id);
//        if (!is_null($user)) {
//            return JsonResponse::unauthorized();
//        }

//        $orders = Order::with('products')->get();
        if (empty($orders)) {
            return JsonResponse::success(
                $message = 'No Products Yet',
            );
        }
        return JsonResponse::success(
            $message = 'Found',
            $data = $orders,
        );
    }

    public function show($id)
    {
        $data = Order::with('products')->find($id);

        if (is_null($data)) {
            return JsonResponse::notFound();
        }

        $user = $this->getAuth();
        if (!is_null($user) and $user->id != $data->customer_id) {
            return JsonResponse::unauthorized();
        }


        return JsonResponse::success(
            $message = 'Found',
            $data,
        );

    }

    public function validation($request)
    {
        return Validator::make($request->all(),
            [
                'discount' => 'numeric',
            ]);
    }

    private function getProductsPrices($products)
    {
        $ids = array_map(function ($product) {
            return $product['id'];
        },
            $products
        );
        return Product::whereIn('id', $ids)->sum('price');
    }

    public function store(Request $request)
    {
        $customer = $this->getAuth();
        if (is_null($customer)) {
            return JsonResponse::unauthorized();
        }
        $validator = $this->validation($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }
//        return $request;
//        $total_price = 1000;

        $total_price = $this->getProductsPrices($request->products);


        $order = Order::create(
            ['discount' => $request->discount,
                'total_price' => $total_price,
                'customer_id' => $customer->id]
        );

        foreach ($request->products as $product)
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);
//        $order->products()->attach($request->products);

        return JsonResponse::success(
            $message = 'Created Successfully',
            $data = Order::with('products')->find($order->id),
        );
    }

    public function update(Request $request, $id)
    {
        $customer = $this->getAuth();
        if ($customer == null) {
            return JsonResponse::unauthorized();
        }
        $data = Order::find($id);
        if (is_null($data)) {
            return JsonResponse::notFound();
        }
        if ($customer->id != $data->customer_id) {
            return JsonResponse::unauthorized();
        }
        $validator = $this->validation($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }


        $data->products()->detach($data->products);

        $total_price = $this->getProductsPrices($request->products);

        $data->update(
            ['discount' => $request->discount,
                'total_price' => $total_price,
                'customer_id' => $customer->id]
        );
        foreach ($request->products as $product)
            $data->products()->attach($product['id'], ['quantity' => $product['quantity']]);

        return JsonResponse::success(
            $message = 'Updated Successfully',
            $data = Order::with('products')->find($id),
        );
    }

    public function destroy($id)
    {
        $customer = $this->getAuth();
        if (is_null($customer)) {
            return JsonResponse::unauthorized();
        }

        $data = Order::find($id);
        if (is_null($data)) {
            return JsonResponse::notFound();
        }

        if ($customer->id != $data->customer_id) {
            return JsonResponse::unauthorized();
        }

        Order::destroy($id);
        return JsonResponse::success(
            $message = 'Deleted Successfully',
        );
    }


//    public function index()
//    {
//    }
//
//    public function show($id)
//    {
//        return
//    }
//
//    public function store(StoreOrderRequest $request)
//    {
//        $order = Order::create($request->validated());
//        $order->products()->attach($request->products);
//        return $order;
//    }
//
//    public function update(UpdateOrderRequest $request,$id)
//    {
//        $order=$this->show($id);
//        $order->products()->detach($order->products);
//        $order->update($request->validated());
//        $order->products()->attach($request->products);
//        return "Success";
//    }
//
//    public function destroy($id)
//    {
//        Order::destroy($id);
//        return "success";
//    }

}
