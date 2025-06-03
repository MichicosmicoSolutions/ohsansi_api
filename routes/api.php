<?php

use App\Http\Controllers\AreasController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\OlympiadsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoletaDePagoController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\OCRController;
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
Route::post('/register', [AuthController::class, 'register']);
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
Route::delete('/areas/{id}', [AreasController::class, 'destroy']);


// Rutas para Categoriess
Route::get('/categories', [CategoriesController::class, 'index']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::get('/area/{area_id}/categories', [CategoriesController::class, 'getCategoriasPorArea']);
Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);

// Rutas para inscripciones -- NO SE USAN
// Route::get('/inscriptions/{id}', [InscriptionController::class, 'show']);
// Route::get('/inscriptions', [InscriptionController::class, 'index']);
// Route::post('/inscriptions', [InscriptionController::class, 'store']);

// Route::post('/inscriptions/multiple', [InscriptionController::class, 'storeMultiple']);
// Route::post('/inscriptions/excel', [InscriptionController::class, 'storeExcel']);


Route::get('/inscription/form', [InscriptionController::class, 'getFormData']);
Route::post('/inscription/olympic', [InscriptionController::class, 'storeOlympic']);
Route::get('/inscription/form', [InscriptionController::class, 'storeOlympicMultiple']);


// PRIMER PASO
// 1. Registrar memoria
// 2. Registrar olimpiada
// 3. Registrar departamento, provincia y colegio

// SEGUNDO PASO
// 1. Registrar estudiante o estudiantes
// 2. Registrar su grado del estudiante
// 3. Registrar tutor legal o apoderado

// TERCER PASO
// 1. Registrar areas y tutores guías

// CUARTO PASO
// 1. Registrar información del responsable del pago (por defecto el tutor legal)

// QUITO PASO
// 1. Mostrar voucher y terminar


// ============================================================

// primer paso escoger olimpiada /check
// segundo paso es registrar carnet y fecha para la memoria /check
Route::post('/olympiads/{id}/inscriptions/init', [InscriptionController::class, 'initInscription']); //primer paso
// tercer paso es registrar al estudiante /check
Route::post('/olympiads/{id}/inscriptions', [InscriptionController::class, 'storeCompetitor']); //segundo paso[]
// cuarto paso es registrar al tutor legal o apoderado /check
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/tutors', [InscriptionController::class, 'storeCompetitorTutor']); //tercer paso[]
// quito paso es registrar dep, prov, col y grado /check
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/schools', [InscriptionController::class, 'storeCompetitorSchool']); //cuarto paso
// sexto paso es registrar areas (una o dos) con su respectivo tutor (opcional)
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/selected-areas', [InscriptionController::class, 'storeAssociatedArea']); //quinto paso[]
// séptimo paso es registrar información del responsable del pago
Route::post('/olympiads/{id}/inscriptions/{inscriptionId}/accountables', [InscriptionController::class, 'storeAccountable']); // sexto paso, se paga todo en uno para el colegio
// octavo paso es generar voucher

// inscription onboarding

Route::get('/olympiads', [OlympiadsController::class, 'index']);
Route::post('/olympiads', [OlympiadsController::class, 'store']);
Route::put('/olympiads/{id}', [OlympiadsController::class, 'update']);
Route::delete('/olympiads/{id}', [OlympiadsController::class, 'destroy']);
Route::get('/olympiads/{id}/areas', [OlympiadsController::class, 'showAreas']);
Route::patch('/olympiads/{id}/price', [OlympiadsController::class, 'updatePrice']);
Route::patch('/olympiads/{id}/publish', [OlympiadsController::class, 'publish']);


Route::get('/olympiads/getOlympicInfo/{id}', [OlympiadsController::class, 'getOlympicInfo']);
Route::get('/olympiads/getAreasByOlympic/{id}', [OlympiadAreasController::class, 'getAreasByOlympic']);
Route::get('/legal-tutor/{ci}', [PersonSearchController::class, 'searchLegalTutor']);

// Rutas para   Buscar
Route::get('/search-student/{ci}', [PersonSearchController::class, 'searchStudent']);

// Rutas De Reportes
//PB 11

Route::get('/inscriptions', [PersonSearchController::class, 'index']);
Route::get('status/{status}', [PersonSearchController::class, 'searchByStatus']);
Route::get('/inscriptions/search/date', [PersonSearchController::class, 'searchByDate']); // con query params from y to
Route::get('/filter', [PersonSearchController::class, 'filter']); // con query params

Route::get('/search-inscriptions/by-area/{area_id}', [PersonSearchController::class, 'searchByArea']);

//PB 12

Route::post('/personal-data', [PersonSearchController::class, 'storePersonalData']);
Route::post('/legal-tutor', [PersonSearchController::class, 'storeLegalTutor']);
// Rutas para  Asociar Olimpiadas y categorias 


Route::post('/olimpiadas-categorias', [OlympiadAreasController::class, 'store']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas/{area_id}/categories', [OlympiadAreasController::class, 'getCategoriesByOlympicAndArea']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas', [OlympiadAreasController::class, 'getAreasByOlympic']);
Route::get('/olimpiadas-categorias/{olympic_id}/areas-categories', [OlympiadAreasController::class, 'getAreasWithCategoriesByOlympic']);

Route::get('/boletas', [BoletaDePagoController::class, 'index']);
Route::post('/boletas', [BoletaDePagoController::class, 'store']);

Route::post('/verificar-comprobante', [OCRController::class, 'verificarComprobante']);


Route::get('/buscar', [PersonSearchController::class, 'getBoletasByCiAndBirthdate']);

Route::get('/inscriptions/filter', function () {
    return response()->json(['message' => 'Route works']);
});
