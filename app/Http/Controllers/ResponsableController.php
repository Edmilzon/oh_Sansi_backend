<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResponsableService;
use Illuminate\Routing\Controller;

class ResponsableController extends Controller {

    protected $responsableService; 

    public function __construct(ResponsableService $responsableService){
        $this->responsableService = $responsableService;
    }

    // GET
    public function index(){
        $responsables = $this->responsableService->getResponsableList(); 
        return response()->json($responsables); 
    }
    
    // POST
   public function store(Request $request, $id_area)
{
    // Separar datos de persona y responsable
    $personaData = $request->input('persona');
    $responsableData = $request->only(['fecha_asignacion', 'activo']);
    $responsableData['id_area'] = $id_area;

    // Crear persona
    $persona = \App\Models\Persona::create($personaData);

    // Crear responsable asociado
    $responsableData['id_persona'] = $persona->id_persona;
    $responsable = $this->responsableService->createNewResponsable($responsableData);

    // Cargar relación persona para devolverla en la respuesta
    $responsable->load('persona');

    return response()->json($responsable, 201);
}

}