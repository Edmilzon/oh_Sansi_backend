<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\ResponsableService;
use Illuminate\Routing\Controller;
use App\Models\Area;
use App\Models\Persona;
use App\Models\CodigoEncargado;  
use App\Models\Responsable;
use Illuminate\Support\Facades\DB;
class ResponsableController extends Controller {

    protected $responsableService; 

    public function __construct(ResponsableService $responsableService){
        $this->responsableService = $responsableService;
    }

    public function index(){
        $responsables = $this->responsableService->getResponsableList(); 
        return response()->json($responsables); 
    }
 public function store(Request $request){
    return DB::transaction(function() use ($request) {
        // 1. Verificar que exista el código encargado
        $codigo = CodigoEncargado::where('codigo', $request->input('codigo_encargado'))->first();
        if (!$codigo) {
            return response()->json([
                'error' => 'Codigo Incorrecto.'
            ], 422);
        }

        // 2. Verificar si ya hay un responsable para esa área
      

        // 3. Validar campos únicos de la persona
         $areaId = $codigo->id_area;
        
          $existeResponsables = Responsable::where('id_area', $areaId)->first();
        $personaData = $request->input('persona');
        if (Persona::where('nombre', $personaData['nombre'])->exists()) {
            if ($existeResponsables) {
            return response()->json([
                'error' => 'El responsable ya está registrado en esta área.'
            ], 422);
        }
            return response()->json(['error' => 'Ya es responsable de una area'], 422);
        }

        $existeResponsable = Responsable::where('id_area', $areaId)->first();

        if ($existeResponsable) {
            return response()->json([
                'error' => 'Ya existe un responsable asignado para esta área.'
            ], 422);
        }
        if (Persona::where('ci', $personaData['ci'])->exists()) {
            return response()->json(['error' => 'Ya existe una persona con ese CI.'], 422);
        }

        if (!empty($personaData['telefono']) && Persona::where('telefono', $personaData['telefono'])->exists()) {
            return response()->json(['error' => 'Ya existe una persona con ese teléfono.'], 422);
        }

        if (!empty($personaData['email']) && Persona::where('email', $personaData['email'])->exists()) {
            return response()->json(['error' => 'Ya existe una persona con ese email.'], 422);
        }

        // 4. Crear persona nueva
        $persona = Persona::create($personaData);

        // 5. Crear responsable_area
        $responsable = Responsable::create([
            'id_persona' => $persona->id_persona,
            'id_area' => $areaId,
            'fecha_asignacion' => $request->input('fecha_asignacion'),
            'activo' => true
        ]);

        // 6. Retornar respuesta con relaciones cargadas
        return response()->json([
            'responsable' => $responsable->load('persona', 'area'),
            'codigo_encargado' => $codigo
        ], 201);
    });
}
}