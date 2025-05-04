<?php

use App\Http\Controllers\AreasController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\OlympiadsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\OlympiadAreasController;
use App\Http\Controllers\PersonSearchController;
use App\Http\Controllers\ResponsableController;

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

// Route::post('/accountable/access', [ResponsableController::class, 'access']);

// Rutas para areas
Route::get('/areas', [AreasController::class, 'index']);
Route::post('/areas', [AreasController::class, 'store']);
Route::patch('/areas/{id}/pricing', [AreasController::class, 'updatePrice']);



// Rutas para Categoriess
Route::get('/categories', [CategoriesController::class, 'index']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::get('/area/{area_id}/categories', [CategoriesController::class, 'getCategoriasPorArea']);
Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);

// Rutas para inscripciones
Route::get('/inscriptions', [InscriptionController::class, 'index']);
Route::get('/inscriptions/{id}', [InscriptionController::class, 'show']);
Route::post('/inscriptions', [InscriptionController::class, 'store']);




Route::get('/olympiads', [OlympiadsController::class, 'index']);
Route::post('/olympiads', [OlympiadsController::class, 'store']);
Route::put('/olympiads/{id}', [OlympiadsController::class, 'update']);
Route::delete('/olympiads/{id}', [OlympiadsController::class, 'destroy']);
Route::get('/olympiads/{id}/areas', [OlympiadsController::class, 'showAreas']);
Route::patch('/olympiads/{id}/price', [OlympiadsController::class, 'updatePrice']);
Route::patch('/olympiads/{id}/publish', [OlympiadsController::class, 'publish']);
# inscription onboarding
Route::post('/olympiads/{id}/inscriptions/init', [InscriptionController::class, 'initInscription']);
Route::post('/olympiads/{id}/inscriptions', [InscriptionController::class, 'storeCompetitor']);
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/schools', [InscriptionController::class, 'storeCompetitorSchool']);
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/tutors', [InscriptionController::class, 'storeCompetitorTutor']);
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/selected-areas', [InscriptionController::class, 'storeAssociatedArea']);
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/accountables', [InscriptionController::class, 'storeAccountable']);


Route::get('/olympiads/getOlympicInfo/{id}', [OlympiadsController::class, 'getOlympicInfo']);
Route::get('/olympiads/getAreasByOlympic/{id}', [OlympiadAreasController::class, 'getAreasByOlympic']);
Route::get('/legal-tutor/{ci}', [PersonSearchController::class, 'searchLegalTutor']);

// Rutas para   Buscar
Route::get('/search-student/{ci}', [PersonSearchController::class, 'searchStudent']);

// Rutas De Reportes
//PB 11
Route::get('/search-inscriptions/by-status/{status}', [PersonSearchController::class, 'searchByStatus']);
Route::get('/search-inscriptions/by-area/{area_id}', [PersonSearchController::class, 'searchByArea']);

Route::get('/search-inscriptions/by-area/{area_id}', [PersonSearchController::class, 'searchByArea']);
//PB 12
Route::get('/search-inscriptions', [PersonSearchController::class, 'index']);
Route::post('/personal-data', [PersonSearchController::class, 'storePersonalData']);
Route::post('/legal-tutor', [PersonSearchController::class, 'storeLegalTutor']);
// Rutas para  Asociar Olimpiadas y categorias 


Route::post('/olimpiadas-categorias', [OlympiadAreasController::class, 'store']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas/{area_id}/categories', [OlympiadAreasController::class, 'getCategoriesByOlympicAndArea']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas', [OlympiadAreasController::class, 'getAreasByOlympic']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas-categories', [OlympiadAreasController::class, 'getAreasWithCategoriesByOlympic']);
