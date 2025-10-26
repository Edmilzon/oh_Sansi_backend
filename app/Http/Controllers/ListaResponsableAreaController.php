<?php

namespace App\Http\Controllers;

use App\Model\Area;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\ListaResponsableAreaService;

class ListaResponsableAreaController extends Controller {

    protected $listaResponsableAreaService;

    public function __construct(ListaResponsableAreaService $listaResponsableAreaService){
        $this->listaResponsableAreaService = $listaResponsableAreaService;
    }

    public function getNivelesPorArea($idArea)
    {
        $niveles = $this->listaResponsableAreaService->getNivelesPorArea((int)$idArea);
        return response()->json($niveles);
    }

    public function getAreaPorResponsable($idResponsable){
        $areas = $this->listaResponsableAreaService->getAreaporResponsable((int)$idResponsable);
        return response()->json($areas);
    }
}
