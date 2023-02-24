<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CustomerController;
use \App\Http\Controllers\OrderController;
use \App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/sign_up', [AuthController::class, 'sign_up']);
    Route::post('/sign_in', [AuthController::class, 'sign_in']);
    Route::post('/sign_out', [AuthController::class, 'sign_out'])->middleware('auth:sanctum');
});

Route::prefix('users')->group(function (){
    Route::get('/index', [UserController::class, 'index']);
    Route::get('/show/{id}', [UserController::class, 'show']);
    Route::post('/store', [UserController::class, 'store']);
    Route::put('/update/{id}', [UserController::class, 'update']);
    Route::delete('/delete/{id}', [UserController::class, 'destroy']);
});

Route::prefix('customer')->group(function (){
    Route::get('/index', [CustomerController::class, 'index']);
    Route::get('/show/{id}', [CustomerController::class, 'show']);
    Route::post('/store', [CustomerController::class, 'store']);
    Route::put('/update/{id}', [CustomerController::class, 'update']);
    Route::delete('/delete/{id}', [CustomerController::class, 'destroy']);
});

Route::prefix('products')->middleware(['auth:sanctum'])->group(function (){
    Route::get('/index', [ProductController::class, 'index']);
    Route::get('/show/{id}', [ProductController::class, 'show']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::put('/update/{id}', [ProductController::class, 'update']);
    Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
});

Route::prefix('orders')->middleware('auth:sanctum')->group(function (){
    Route::get('/index', [OrderController::class, 'index']);
    Route::get('/show/{id}', [OrderController::class, 'show']);
    Route::post('/store', [OrderController::class, 'store']);
    Route::put('/update/{id}', [OrderController::class, 'update']);
    Route::delete('/delete/{id}', [OrderController::class, 'destroy']);
});
