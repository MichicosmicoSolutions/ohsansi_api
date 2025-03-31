<?php

use App\Http\Controllers\AreasController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/categorias',[CategoriaController::class,'index']);
Route::get('/areas',[AreasController::class,'index']);
Route::post('/areas', [AreasController::class, 'store']);
Route::post('/categorias', [CategoriaController::class, 'store']);