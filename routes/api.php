<?php

use App\Http\Controllers\AreasController;
use App\Http\Controllers\CategoriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\OlimpiadasCategoriController;
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
//PB14
// Rutas para areas
Route::get('/olympics', [OlympicsController::class, 'index']);
Route::post('/olympics', [OlympicsController::class, 'store']);
Route::put('/olympics/{id}', [OlympicsController::class, 'update']);
// PB 02
Route::patch('/olympics/{id}/price', [OlympicsController::class, 'updatePrice']);
//PB 16
Route::patch('/olympics/{id}/publish', [OlympicsController::class, 'publish']);

Route::get('/olympics/getOlympicInfo/{id}', [OlympicsController::class, 'getOlympicInfo']);




// Rutas para   Buscar
Route::get('/search-student/{ci}', [PersonSearchController::class, 'searchStudent']);

// Rutas De Reportes
//PB 11
Route::get('/search-inscriptions/by-status/{status}', [PersonSearchController::class, 'searchByStatus']);
Route::get('/search-inscriptions/by-area/{area_id}', [PersonSearchController::class, 'searchByArea']);

Route::get('/search-inscriptions/by-area/{area_id}', [PersonSearchController::class, 'searchByArea']);
//PB 12
Route::get('/search-inscriptions', [PersonSearchController::class, 'index']);

// Rutas para  Asociar Olimpiadas y categorias 
//PB 15
Route::post('/olimpiadas-categorias', [OlimpiadasCategoriController::class, 'store']);
