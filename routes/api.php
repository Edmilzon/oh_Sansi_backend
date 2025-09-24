<?php

use app\Http\Controllers\ImportarcsvController;
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
Route::post('/importarcsv', [ImportarcsvController::class, 'importar']);
