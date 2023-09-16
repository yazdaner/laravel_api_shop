<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Route::post('/register',[AuthController::class,'register'])->name('register');
// Route::post('/login',[AuthController::class,'login'])->name('login');
// Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum')->name('logout');


Route::apiResource('brands',BrandController::class);

Route::get('/brands/{brand}/products',[BrandController::class,'products']);



Route::apiResource('categories',CategoryController::class);

Route::get('/categories/{category}/children',[CategoryController::class,'children']);
Route::get('/categories/{category}/parent',[CategoryController::class,'parent']);

Route::get('/categories/{category}/products',[CategoryController::class,'products']);


Route::apiResource('products',ProductController::class);

Route::post('/payment/send',[PaymentController::class,'send']);

Route::post('/payment/verify',[PaymentController::class,'verify']);



Route::post('/register',[AuthController::class,'register'])->name('register');

Route::post('/login',[AuthController::class,'login'])->name('login');

Route::post('/logout',[AuthController::class,'logout'])->middleware(['auth:sanctum'])->name('logout');


Route::get('/users',[UserController::class,'users']);
Route::get('/users/{user}',[UserController::class,'show']);
