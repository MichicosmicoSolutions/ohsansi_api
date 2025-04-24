<?php

use App\Http\Controllers\AreasController;
use App\Http\Controllers\CategoriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\OlympicsController;
use App\Http\Controllers\PersonSearchController;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Rutas para areas
Route::get('/areas', [AreasController::class, 'index']);
Route::post('/areas', [AreasController::class, 'store']);
Route::patch('/areas/{id}/pricing', [AreasController::class, 'updatePrice']);



// Rutas para Categoriess
Route::get('/categories', [CategoriesController::class, 'index']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::get('/area/{area_id}/categories', [CategoriesController::class, 'getCategoriasPorArea']);


// Rutas para inscripciones
Route::get('/inscriptions', [InscriptionController::class, 'index']);
Route::post('/inscriptions', [InscriptionController::class, 'store']);


// Rutas para  excel
Route::post('/inscriptions/excel', [ExcelController::class, 'store']);
Route::get('/inscriptions/excel/template', [ExcelController::class, 'downloadTemplate']);


// Rutas para  Olympiadas
Route::post('/olympics', [OlympicsController::class, 'store']);
Route::put('/olympics/{id}', [OlympicsController::class, 'update']);
// Rutas para   Buscar
Route::get('/students/by-ci/{ci}', [PersonSearchController::class, 'searchStudent']);