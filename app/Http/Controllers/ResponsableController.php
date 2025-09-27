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

    public function index(){
        $responsables = $this->responsableService->getResponsableList(); 
        return response()->json($responsables); 
    }
    
   public function store(Request $request, $id_area){

    $personaData = $request->input('persona');
    $responsableData = $request->only(['fecha_asignacion', 'activo']);
    $responsableData['id_area'] = $id_area;

    $persona = \App\Models\Persona::create($personaData);

    $responsableData['id_persona'] = $persona->id_persona;
    $responsable = $this->responsableService->createNewResponsable($responsableData);

    $responsable->load('persona');

    return response()->json($responsable, 201);
  }
}