<?php

namespace App\Http\Controllers;

use App\Http\Json\JsonResponse;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function Termwind\ValueObjects\pr;


class OrderController extends Controller
{

    private ProductController $productController;

    public function __construct(ProductController $productController)
    {
        $this->productController = $productController;
    }

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
            unset($p['pivot']);

            $images = $this->productController->getImages($product['id']);
            $p['images'] = $images;
            return $p;
        }, $data->products->toArray()
        );

        $quantities = array_map(function ($product) {
            return $product['pivot']['quantity'];
        }, $data->products->toArray()
        );

        $order = Order::find($data->id);
        $order['products'] = $products;
        $order['quantities'] = $quantities;
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
            array_push($customOrders, $this->customData($data));
        }

        return JsonResponse::success(
            $message = 'Found',
            $data = $customOrders,
        );
    }

    public function show($id)
    {
        $order = Order::with('products')->find($id);


        $order = $this->customData($order);
        if (is_null($order)) {
            return JsonResponse::notFound();
        }

        $user = $this->getAuth();
        if (!is_null($user) and $user->id != $order->customer_id) {
            return JsonResponse::unauthorized();
        }


        return JsonResponse::success(
            $message = 'Found',
            $order,
        );

    }

    private function getProductsPrices($products)
    {
        $productsInfo = Product::whereIn('id', array_column($products, 'id'))->get();

        $prices = array_map(
            function ($product, $quantity) {
                return $product['price'] * $quantity;
            },
            $productsInfo->toArray(), array_column($products, 'quantity')
        );

        return array_sum($prices);
    }

    function validator(Request $request)
    {
        return Validator::make($request->all(),
            [
                'products' => 'required',
//                'products.*' => 'required|numeric'
            ]);
    }

    public function store(Request $request)
    {
        $customer = $this->getAuth();
        if (is_null($customer)) {
            return JsonResponse::unauthorized();
        }

        $validator = $this->validator($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }

        $total_price = $this->getProductsPrices($request->products);

        $data = [
            'total_price' => $total_price,
            'customer_id' => $customer->id
        ];

        $order = Order::create($data);


        foreach ($request->products as $product)
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);


        $order = Order::with('products')->find($order->id);
        $order = $this->customData($order);
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

        $validator = $this->validator($request);
        if ($validator->fails()) {
            return JsonResponse::validationError($validator->errors());
        }

        $order->products()->detach($order->products);


        $total_price = $this->getProductsPrices($request->products);
        $data = [
            'total_price' => $total_price,
            'customer_id' => $customer->id
        ];

        if ($request->discount != null)
            $data['discount'] = $request->discount;


        foreach ($request->products as $product)
            $order->products()->attach($product['id'], ['quantity' => $product['quantity']]);

        $order->update($data);

        $order = Order::with('products')->find($order->id);
        $order = $this->customData($order);
        return JsonResponse::success(
            $message = 'Updated Successfully',
            $data = $order,
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
