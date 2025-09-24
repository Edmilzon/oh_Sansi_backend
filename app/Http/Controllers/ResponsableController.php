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
    public function store(Request $request, $id_area){
    $data = $request->only(['id_persona', 'fecha_asignacion', 'activo']);
    $data['id_area'] = $id_area;

    $responsable = $this->responsableService->createNewResponsable($data);

    return response()->json($responsable, 201);
}
}