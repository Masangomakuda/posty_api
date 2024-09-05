<?php

use Illuminate\Http\Request;
use PHPUnit\Metadata\PostCondition;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Authcontroller as ControllersAuthcontroller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('login',[AuthController::class,'login'])->name('login');
Route::post('register',[AuthController::class,'register'])->name('register');
Route::post('logout',[AuthController::class,'logout'])->name('logout')->middleware('auth:sanctum');


Route::group(['prefix'=>'v1', 'middleware'=>'auth:sanctum'], function(){


Route::get('/user', function (Request $request) { return $request->user(); });
Route::apiResource('posts',PostController::class);
Route::apiResource('users',UserController::class);
Route::get('/posts/search/{searchword}',[PostController::class,'search']);
Route::post('/posts/like/{id}',[PostController::class,'like']);


});


Route::fallback(function(){
    return response()->json([
        'message' => 'Route Not Found. If error persists, contact Kudam775@gmail.com'], 404);
});