<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/payment/verify', function (Request $request) {
//     // dd($request->all());
//     $respones = Http::post('http://localhost:8000/api/payment/verify',[
//         'token' => $request->token,
//         'status' => $request->status
//     ]);
//     dd($respones);
// });

