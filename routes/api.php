<?php

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


use App\Http\Controllers\ProductController;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);

// Rutas para la gestión de niveles y áreas

use App\Http\Controllers\NivelController;

Route::get('/niveles', [NivelController::class, 'index']);
Route::post('/niveles', [NivelController::class, 'store']);
