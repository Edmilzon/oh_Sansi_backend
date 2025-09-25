<?php

use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\ProductController;
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
Route::prefix('v1')->group(function () { // Ejemplo de versionado de API
    Route::apiResource('evaluadores', EvaluadorController::class)->only(['store']);
    // Otras rutas de la versión 1
});

// Si no quieres versionar, puedes dejarlo como estaba o agrupar por middleware si aplica
// Route::apiResource('evaluadores', EvaluadorController::class)->only(['store']);
