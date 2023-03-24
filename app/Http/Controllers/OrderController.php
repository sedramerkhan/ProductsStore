<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use function Termwind\ValueObjects\pr;


class OrderController extends Controller
{
    private function getAuth()
    {
        $user = auth()->user();
        if (!is_null($user))
            return $user->type == 'c' ? $user : null;
        return null;
    }

    private function customData($data)
    {
        $products = array_map(function ($product) {
            $p = $product;
            $p['quantity'] = $product['pivot']['quantity'];
            unset($p['pivot']);
            return $p;
        }, $data->products->toArray()
        );

        $order = Order::find($data->id);
        $order['products'] = $products;
        return $order;
    }

    public function index()
    {
        $user = $this->getAuth();

        $orders = is_null($user) ? Order::with('products')->get() :
            Order::with('products')->where('customer_id', $user->id);
        if (empty($orders)) {
            return JsonResponse::failure(
                $message = 'No Products Yet', 400
            );
        }

        $customOrders = [];
        foreach ($orders as $data) {
            array_push($customOrders ,$this->customData($data));
        }

        return JsonResponse::success(
            $message = 'Found',
            $data = $customOrders,
        );
    }

    public function show($id)
    {
        $data = Order::with('products')->find($id);

        $order = $this->customData($data);
        if (is_null($data)) {
            return JsonResponse::notFound();
        }

        $user = $this->getAuth();
        if (!is_null($user) and $user->id != $data->customer_id) {
            return JsonResponse::unauthorized();
        }


        return JsonResponse::success(
            $message = 'Found',
            $order,
        );

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

    public function store(OrderRequest $request)
    {
        $customer = $this->getAuth();
        if (is_null($customer)) {
            return JsonResponse::unauthorized();
        }

        $total_price = $this->getProductsPrices($request->products);

        $data = [
            'total_price' => $total_price,
            'customer_id' => $customer->id
        ];

        $order = Order::create($data);

        foreach ($request->products as $product)
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);


        $order['products'] = $request->products;
        return JsonResponse::success(
            $message = 'Created Successfully',
            $data = $order,
        );
    }

    public function update(Request $request, $id)
    {
        $customer = $this->getAuth();
        if (is_null($customer)) {
            return JsonResponse::unauthorized();
        }
        $order = Order::find($id);
        if (is_null($order)) {
            return JsonResponse::notFound();
        }
        if ($customer->id != $order->customer_id) {
            return JsonResponse::unauthorized();
        }
//        $validator = $this->validation($request);
//        if ($validator->fails()) {
//            return JsonResponse::validationError($validator->errors());
//        }


        $order->products()->detach($order->products);

        $total_price = $this->getProductsPrices($request->products);
        $data = [
            'total_price' => $total_price,
            'customer_id' => $customer->id
        ];

        if ($request->discount != null)
            $data['discount'] = $request->discount;

        $order->update($data);
        foreach ($request->products as $product)
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);

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
