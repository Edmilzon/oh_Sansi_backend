<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResponsableController;
// use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\NivelController;
// use App\Http\Controllers\ProductController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaOlimpiadaController;
use App\Http\Controllers\EvaluadorController;
// use App\Http\Controllers\ResponsableController;
// use App\Http\Controllers\Responsable\CompetidorController as ResponsableCompetidorController;
// use App\Http\Controllers\ImportarcsvController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\AreaNivelController;
use App\Http\Controllers\ListaResponsableAreaController;
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

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Rutas para usuarios
Route::prefix('usuarios')->group(function () {
    Route::get('ci/{ci}', [AuthController::class, 'getUserByCi']);
});

// Rutas para responsables de área
Route::prefix('responsables')->group(function () {
    Route::post('/', [ResponsableController::class, 'store']);
    Route::get('/', [ResponsableController::class, 'index']);
    Route::get('/{id}', [ResponsableController::class, 'show']);
    Route::get('/ci/{ci}/gestiones', [ResponsableController::class, 'getGestionesByCi']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [ResponsableController::class, 'getAreasByCiAndGestion']);
});

// Rutas para evaluadores
Route::prefix('evaluadores')->group(function () {
    Route::post('/', [EvaluadorController::class, 'store']);
    Route::get('/', [EvaluadorController::class, 'index']);
    Route::get('/{id}', [EvaluadorController::class, 'show']);
});

Route::get('olimpiadas/{identifier}/areas', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);

//Rutas para la gestión de niveles
Route::apiResource('niveles', NivelController::class)->only(['index', 'store']);

//area mostrar y insertar
Route::get('/area', [AreaController::class, 'index']);
Route::post('/area', [AreaController::class, 'store']);

// Rutas comentadas temporalmente hasta que se creen los controladores
/*
// Rutas para la gestión de productos
Route::apiResource('products', ProductController::class)->only(['index', 'store']);


// Rutas para la gestión de evaluadores
Route::prefix('v1')->group(function () {
    Route::apiResource('evaluadores', EvaluadorController::class)->only(['store']);
});

//area mostrar y insertar
Route::get('/areas/{gestion}', [AreaController::class, 'getAreasPorGestion']);

//responsable de area mostrar y insertar 
Route::get('/responsableArea', [ResponsableController::class, 'index']);
Route::post('/responsableArea', [ResponsableController::class, 'store']);
Route::get('/usuarios/roles/{ci}', [ResponsableController::class, 'showRolesByCi']);

// Competidores por Responsable de Área
Route::get('/responsables/{id_persona}/competidores', [ResponsableCompetidorController::class, 'index']);

//Importar csv
Route::post('/competidores/importar',[ImportarcsvController::class,'importar']);
Route::get('/competidores', [ImportarcsvController::class, 'index']);

//Rutas asociacion area - nivel
/*Route::apiResource('nivel',NivelController::class)->only(['index']);*/
Route::post('area-niveles', [AreaNivelController::class, 'store']);
/*Route::get('area-niveles/{id_area}', [AreaNivelController::class, 'getByArea']);
Route::put('area-niveles/{id_area}', [AreaNivelController::class, 'updateByArea']);*/
Route::get('area-niveles/{id_area}', [AreaNivelController::class, 'getByAreaAll']);
Route::get('/areas-con-niveles', [AreaNivelController::class, 'getAreasConNiveles']);
Route::get('/area-nivel', [AreaNivelController::class, 'getAreasConNivelesSimplificado']);
Route::get('/area-nivel/{id_olimpiada}', [AreaNivelController::class, 'getAreasConNivelesPorOlimpiada']);
Route::get('/area-nivel/gestion/{gestion}', [AreaNivelController::class, 'getAreasConNivelesPorGestion']);

//Rutas Parametros de clasificación
Route::get('/areas-olimpiada/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/areas-gestion', [AreaOlimpiadaController::class, 'getAreasGestionActual']);
Route::get('/areas-nombres', [AreaOlimpiadaController::class, 'getNombresAreasGestionActual']);
Route::get('/parametros/gestion-actual', [ParametroController::class, 'getParametrosGestionActual']);
Route::get('/parametros/{idOlimpiada}', [ParametroController::class, 'getByOlimpiada']);

//lista de competidores
Route::get('/responsable/{idResponsable}', [ListaResponsableAreaController::class, 'getAreaPorResponsable']);
Route::get('/niveles/{idArea}', [ListaResponsableAreaController::class, 'getNivelesPorArea']);
Route::get('/listaCompleta/{idArea}/{idNivel}', [ListaResponsableAreaController::class, 'getEstudiantes']);