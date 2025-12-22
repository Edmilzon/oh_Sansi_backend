<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\OlimpiadaController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaOlimpiadaController;
use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\ImportarcsvController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\AreaNivelController;
use App\Http\Controllers\ListaResponsableAreaController;
use App\Http\Controllers\GradoEscolaridadController;
use App\Http\Controllers\FaseController;
use App\Http\Controllers\MedalleroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\AreaNivelGradoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolAccionController;
use App\Http\Controllers\AccionDisponibilidadController;
use App\Http\Controllers\SistemaEstadoController;
use App\Http\Controllers\UsuarioAccionesController;
use App\Http\Controllers\CronogramaFaseController;
use App\Http\Controllers\FaseGlobalController;
// Nuevos Controladores
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CompetidorController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\DescalificacionController;
use App\Http\Controllers\ConfiguracionAccionController;
use App\Http\Controllers\AccionSistemaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
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

Route::prefix('auth')->group(function () {
    Route::post('login', [UsuarioController::class, 'login']);
    Route::middleware('auth:sanctum')->get('me', [UsuarioController::class, 'me']);
});
Route::get('/usuarios/ci/{ci}', [UsuarioController::class, 'showByCi']);

// COMPETENCIA
Route::controller(CompetenciaController::class)->prefix('competencias')->group(function () {
    // 1. Gestión Básica (CRUD)
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');

    // 2. Máquina de Estados
    Route::patch('/{id}/publicar', 'publicar');
    Route::patch('/{id}/iniciar', 'iniciar');

    // 3. Cierre y Resultados
    Route::post('/{id}/cerrar', 'cerrar');
    Route::post('/{id}/avalar', 'avalar');

    // Fases Clasificatorias
    Route::get('/fase-global/clasificatoria/actuales', 'fasesClasificatorias');

    // Áreas por Responsable
    Route::get('/responsable/{id_user}/areas/actuales', 'areasResponsable');

    // NIVELES
    Route::get('/area/{id_area}/niveles', 'nivelesPorArea');

    // 4. Filtros Dashboard
    Route::get('/responsable/{id_responsable}/area/{id_area}', 'indexPorResponsable');

    Route::get('/responsable/{id_user}/areas-niveles-competencia', 'areasNivelesCreados');
});

// EXÁMENES
Route::controller(ExamenController::class)->prefix('examenes')->group(function () {

    // 1. Listar exámenes de una competencia
    Route::get('/competencias/{competenciaId}', 'index');

    // 2. CRUD Básico
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');

    // 3. Control Operativo (Interruptores)
    Route::patch('/{id}/iniciar', 'iniciar');
    Route::patch('/{id}/finalizar', 'finalizar');

    // 4. Filtros y Combos
    Route::get('/area-nivel/{id_area_nivel}', 'indexPorAreaNivel');
    Route::get('/combo/area-nivel/{id}', 'comboPorAreaNivel');

    // 5. Sala de Evaluación
    Route::get('/{id}/competidores', 'competidoresPorExamen');
});

// EVALUADORES

// Dashboard Operativo (Lo que ve el juez al entrar)
Route::get('/evaluadores/dashboard', [EvaluadorController::class, 'dashboard']);

// Gestión Administrativa de Evaluadores
Route::prefix('evaluadores')->group(function () {
    Route::post('/', [EvaluadorController::class, 'store']);
    Route::get('/', [EvaluadorController::class, 'index']);
    Route::get('/{id}', [EvaluadorController::class, 'show']);
    Route::put('/ci/{ci}', [EvaluadorController::class, 'updateByCi']);
    Route::get('/{id}/areas-niveles', [EvaluadorController::class, 'getAreasNivelesById']);
    Route::post('/ci/{ci}/areas', [EvaluadorController::class, 'addAreasByCi']);
    Route::get('/ci/{ci}/gestiones', [EvaluadorController::class, 'getGestionesByCi']);
    Route::post('/ci/{ci}/asignaciones', [EvaluadorController::class, 'addAsignaciones']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [EvaluadorController::class, 'getAreasByCiAndGestion']);

    Route::get('/{id}/asignaciones-agrupadas', [EvaluadorController::class, 'getAsignacionesAgrupadas']);
});

// SALA DE EVALUACIÓN
Route::prefix('sala-evaluacion')->controller(EvaluacionController::class)->group(function () {
    Route::get('/examen/{id_examen}', 'index');      // Pizarra
    Route::post('/{id}/bloquear', 'bloquear');       // Semáforo Rojo
    Route::post('/{id}/guardar', 'guardarNota');     // Semáforo Verde + Guardado
    Route::post('/{id}/desbloquear', 'desbloquear'); // Cancelar
    Route::post('/{id}/descalificar', 'descalificar'); // Tarjeta Roja (Expulsión)
    Route::get('/evaluador/{id_user}/areas-niveles', 'listarAreasNiveles'); // niveles y areas filtrados
});

// Responsables de Área
Route::prefix('responsables')->group(function () {
    Route::post('/', [ResponsableController::class, 'store']);
    Route::get('/', [ResponsableController::class, 'index']);
    Route::get('/{id}', [ResponsableController::class, 'show']);
    Route::get('/ci/{ci}/gestiones', [ResponsableController::class, 'getGestionesByCi']);
    Route::put('/ci/{ci}', [ResponsableController::class, 'updateByCi']);
    Route::post('/ci/{ci}/areas', [ResponsableController::class, 'addAreas']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [ResponsableController::class, 'getAreasByCiAndGestion']);
    Route::get('/areas/ocupadas/gestion/actual', [ResponsableController::class, 'getOcupadasEnGestionActual']);
    Route::get('/{id_usuario}/areas-con-niveles/olimpiada-actual', [ResponsableController::class, 'areasConNivelesPorOlimpiadaActual']);
});

// Olimpiadas y Estructura
Route::prefix('olimpiadas')->group(function () {

    Route::get('/', [OlimpiadaController::class, 'index']);
    Route::post('/', [OlimpiadaController::class, 'store']);
    Route::patch('/{id}/activar', [OlimpiadaController::class, 'activar']);
    Route::put('/{id}/activar', [OlimpiadaController::class, 'activar']);
    Route::post('/admin', [OlimpiadaController::class, 'storeAdmin']);
});

Route::get('olimpiadas/{identifier}/areas', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/olimpiadas/anteriores', [OlimpiadaController::class, 'olimpiadasAnteriores']);
Route::get('/olimpiadas/actual', [OlimpiadaController::class, 'olimpiadaActual']);
Route::get('/gestiones', [OlimpiadaController::class, 'gestiones']);

Route::apiResource('niveles', NivelController::class)->only(['index', 'store']);

Route::get('/area', [AreaController::class, 'index']);
Route::post('/area', [AreaController::class, 'store']);
Route::get('/area/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/area/gestion/{gestion}', [AreaOlimpiadaController::class, 'getAreasByGestion']);

Route::get('/niveles/{id_nivel}', [NivelController::class, 'show']);

Route::get('/grados-escolaridad', [GradoEscolaridadController::class, 'index']);
Route::get('/grados-escolaridad/{id_grado_escolaridad}', [GradoEscolaridadController::class, 'show']);

Route::post('importar/{gestion}',[ImportarcsvController::class,'importar']);

// Área Nivel
Route::get('/area-nivel/show/{id}', [AreaNivelController::class, 'show']);
Route::get('/area-nivel/actuales', [AreaNivelController::class, 'getActuales']);
Route::get('/area-nivel/detalle', [AreaNivelController::class, 'getAllWithDetails']);
Route::get('/area-nivel/por-area/{id_area}', [AreaNivelController::class, 'getByArea']);
Route::get('/area-nivel/{id_olimpiada}', [AreaNivelController::class, 'getAreasConNivelesPorOlimpiada']);
Route::get('/area-nivel/gestion/{gestion}', [AreaNivelController::class, 'getAreasConNivelesPorGestion']);
Route::put('/area-nivel/{id}', [AreaNivelController::class, 'update']);
Route::put('/area-nivel/por-area/{id_area}', [AreaNivelController::class, 'updateByArea']);

// Área Nivel Grado
Route::get('/area-nivel', [AreaNivelGradoController::class, 'index']);
Route::post('/area-nivel', [AreaNivelGradoController::class, 'store']);
Route::get('/area-nivel/sim/simplificado', [AreaNivelGradoController::class, 'getAreasConNivelesSimplificado']);
Route::get('/area-nivel/gestion/{gestion}/area/{id_area}', [AreaNivelGradoController::class, 'getNivelesGradosByAreaAndGestion']);
Route::post('/area-nivel/gestion/{gestion}/areas', [AreaNivelGradoController::class, 'getNivelesGradosByAreasAndGestion']);
Route::post('/area-nivel/por-gestion', [AreaNivelGradoController::class, 'getByGestionAndAreas']);
Route::get('/area-niveles/{id_area}', [AreaNivelGradoController::class, 'getByAreaAll']);
Route::get('/areas-con-niveles', [AreaNivelGradoController::class, 'getAreasConNiveles']);

// Parámetros
Route::get('/areas-olimpiada/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/areas-gestion', [AreaOlimpiadaController::class, 'getAreasGestionActual']);
Route::get('/areas-nombres', [AreaOlimpiadaController::class, 'getNombresAreasGestionActual']);
Route::get('/parametros/gestion-actual', [ParametroController::class, 'getParametrosGestionActual']);
Route::get('/parametros/gestiones', [ParametroController::class, 'getAllParametrosByGestiones']);
Route::get('/parametros/an/area-niveles', [ParametroController::class, 'getParametrosByAreaNiveles']);
Route::get('/parametros/{idOlimpiada}', [ParametroController::class, 'getByOlimpiada']);
Route::post('/parametros', [ParametroController::class, 'store']);

// Listas
Route::get('/responsable/{idResponsable}', [ListaResponsableAreaController::class, 'getAreaPorResponsable']);
Route::get('/niveles/{idArea}/area', [ListaResponsableAreaController::class, 'getNivelesPorArea']);
Route::get('/grados/{idArea}/nivel/{idNivel}', [ListaResponsableAreaController::class, 'getListaGrados']);
Route::get('/departamento', [ListaResponsableAreaController::class, 'getDepartamento']);
Route::get('/generos', [ListaResponsableAreaController::class, 'getGenero']);
Route::get('/listaCompleta/{idResponsable}/{idArea}/{idNivel}/{idGrado}/{genero?}/{departamento?}', [ListaResponsableAreaController::class, 'listarPorAreaYNivel']);
Route::get('/competencias/{id_competencia}/area/{idArea}/nivel/{idNivel}/competidores', [ListaResponsableAreaController::class, 'getCompetidoresPorAreaYNivel']);

// Medallero y Reportes
Route::get('/responsableGestion/{idResponsable}', [MedalleroController::class, 'getAreaPorResponsable']);
Route::get('/medallero/area/{idArea}/niveles', [MedalleroController::class, 'getNivelesPorArea']);
Route::post('/medallero/configuracion', [MedalleroController::class, 'guardarMedallero']);

// Descalificaciones (Reporte Unificado)
Route::get('/descalificados', [DescalificacionController::class, 'index']);
Route::post('/descalificados', [DescalificacionController::class, 'store']);

Route::prefix('reportes')->group(function () {
    Route::get('/historial-calificaciones', [ReporteController::class, 'historialCalificaciones']);
    Route::get('/areas', [ReporteController::class, 'getAreas']);
    Route::get('/areas/{idArea}/niveles', [ReporteController::class, 'getNivelesPorArea']);
    Route::get('/competencia/{id}/ranking', [ReporteController::class, 'ranking']);
    Route::get('/evaluacion/{id}/historial', [ReporteController::class, 'historialCambios']);
});

// Extras
Route::apiResource('departamentos', DepartamentoController::class);
Route::apiResource('grados-escolaridad', GradoEscolaridadController::class);
Route::apiResource('instituciones', InstitucionController::class);
Route::get('/areas/actuales', [AreaController::class, 'getActualesPlanas']);
Route::get('/area-nivel/olimpiada/{id_olimpiada}/area/{id_area}', [AreaNivelController::class, 'getNivelesPorAreaOlimpiada']);
// Roles y Permisos
Route::prefix('roles/{idRol}/acciones')->group(function () {
    Route::get('/', [RolAccionController::class, 'index']);
    Route::post('/', [RolAccionController::class, 'store']);
    Route::delete('/{idAccion}', [RolAccionController::class, 'destroy']);
});

Route::get(
    'rol/{id_rol}/fase-global/{id_fase_global}/gestion/{id_gestion}',
    [AccionDisponibilidadController::class, 'index']
);

Route::get('/sistema/estado', [SistemaEstadoController::class, 'index']);

Route::get(
    'usuario/{id_usuario}/fase-global/{id_fase_global}/gestion/{id_gestion}/acciones',
    [UsuarioAccionesController::class, 'index']
);

// Cronogramas
Route::controller(CronogramaFaseController::class)->prefix('cronograma-fases')->group(function () {
    Route::get('/actuales', 'listarActuales');
    Route::get('/', 'index');              // Listar todos
    Route::post('/', 'store');             // Crear nuevo
    Route::get('/{id}', 'show');           // Ver uno específico
    Route::put('/{id}', 'update');         // Actualizar (soporta datetime)
    Route::delete('/{id}', 'destroy');     // Eliminar
});

// Endpoint Custom para WebSockets (Reemplaza al /broadcasting/auth nativo)
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);

// Enpoint para obtener la gestion actual y dervivados
Route::get('/sistema/estado', [SistemaEstadoController::class, 'index']);

// Fase Global
Route::controller(FaseGlobalController::class)->prefix('fase-global')->group(function () {
    Route::post('/configurar', 'storeCompleto');
    Route::get('/actuales', 'listarActuales');
    Route::get('/{id}', 'show');
    Route::patch('/{id}/cronograma', 'updateCronograma');
});

// MODULO DE REPORTES (AUDITORIA Y RESULTADOS)
Route::prefix('reportes')->controller(ReporteController::class)->group(function () {
    Route::get('/historial-calificaciones', 'historialCalificaciones');
    Route::get('/competencia/{id}/ranking', 'ranking');
    Route::get('/evaluacion/{id}/historial', 'historialCambios');
    Route::get('/areas', 'getAreas');
    Route::get('/areas/{idArea}/niveles', 'getNivelesPorArea');
});

Route::prefix('configuracion-acciones')->controller(ConfiguracionAccionController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/','update');
});

Route::prefix('roles')->controller(RolAccionController::class)->group(function () {
    Route::get('/matriz', 'index');
    Route::post('/matriz', 'updateGlobal');
});

Route::get('/acciones-sistema', [AccionSistemaController::class, 'index']);
