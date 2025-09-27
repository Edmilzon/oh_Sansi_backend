<?php

use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ImportarcsvController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/test', function () {
    return response()->json([
        'message' => '¡OhSansi Backend API funcionando correctamente!',
        'status' => 'success',
        'timestamp' => now()
    ]);
});

Route::get('/', function () {
    return response()->json(['message' => 'API funcionando correctamente']);
});

// Rutas para la gestión de productos
Route::apiResource('products', ProductController::class)->only(['index', 'store']);

// Rutas para la gestión de niveles
Route::apiResource('niveles', NivelController::class)->only(['index', 'store']);

// Rutas para la gestión de evaluadores
Route::prefix('v1')->group(function () {
    Route::apiResource('evaluadores', EvaluadorController::class)->only(['store']);

});

//area mostrar y insertar
Route::get('/area', [AreaController::class, 'index']);
Route::post('/area', [AreaController::class, 'store']);

//responsable de area mostrar y insertar 
Route::get('/responsableArea', [ResponsableController::class, 'index']);
Route::post('/responsableArea', [ResponsableController::class, 'store']);

//Importar csv
Route::post('/competidores/importar',[ImportarcsvController::class,'importar']);
Route::get('/competidores', [CompetidorController::class, 'index']);