<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function show($id)
    {
        return  User::find($id);
    }

    public function store(Request $request)
    {
        return User::create(array_merge(
            $request->except('password'),
            ['password'=>bcrypt($request->password)]
        ));
    }

    public function update(Request $request,$id)
    {
         User::find($id)->update(array_merge(
            $request->except('password'),
            ['password'=>bcrypt($request->password)]
        ));

         return User::find($id);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return "success";
    }

}
