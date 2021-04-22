<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('register', [App\Http\Controllers\UserController::class, 'register']);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [App\Http\Controllers\UserController::class, 'logout']);
    Route::get('getuser', [App\Http\Controllers\UserController::class, 'getuser']);
    Route::put('update/{user}', [App\Http\Controllers\UserController::class, 'Update']);
    Route::get('show/{id}',[App\Http\Controllers\UserController::class, 'show']);
    Route::delete('delete/{id}',[App\Http\Controllers\UserController::class, 'delete']);
});
Route::get('showall', [App\Http\Controllers\UserController::class, 'getuser']);
Route::get('emailreset',[App\Http\Controllers\UserController::class,'emailreset']);
Route::patch('/reset/{user}',[App\Http\Controllers\UserController::class,'resetpassword']);
