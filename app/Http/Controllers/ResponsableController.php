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
use App\Http\Requests\ResponsableRequest;

class ResponsableController extends Controller {

    protected $responsableService; 

    public function __construct(ResponsableService $responsableService){
        $this->responsableService = $responsableService;
    }

    public function index(){
        $responsables = $this->responsableService->getResponsableList(); 
        return response()->json($responsables); 
    }

    public function store(ResponsableRequest $request)
    {
        $validatedData = $request->validated();
        $responsable = $this->responsableService->createNewResponsable($validatedData);

        return response()->json([
                'responsable' => $responsable
            ], 201);
    }
}