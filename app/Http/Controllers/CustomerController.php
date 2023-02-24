<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::with('products')->all();
    }

    public function show($id)
    {
        return  Customer::find($id);
    }

    public function store(Request $request)
    {
        return Customer::create(array_merge(
            $request->except('password'),
            ['password'=>bcrypt($request->password)]
        ));
    }

    public function update(Request $request,$id)
    {
        Customer::find($id)->update(array_merge(
            $request->except('password'),
            ['password'=>bcrypt($request->password)]
        ));
        return "Success";
    }

    public function destroy($id)
    {
        Customer::destroy($id);
        return "success";
    }
}
