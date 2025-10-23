<?php

namespace App\Services;

use App\Models\Olimpiada;
use Illuminate\Support\Facades\DB;

class OlimpiadaService {
    
    public function obtenerOlimpiadaActual() {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiadas Oh! Sansi $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => "Gestión $gestionActual"],
            ['nombre' => $nombreOlimpiada]
        );
    }
    
    public function obtenerOlimpiadaPorGestion($gestion) {

        $nombreOlimpiada = "Olimpiadas Oh! Sansi " . substr($gestion, -4);
        return Olimpiada::firstOrCreate(
            ['gestion' => $gestion],
            ['nombre' => $nombreOlimpiada]
        );
    }
    
    public function verificarGestionExiste($gestion) {
        return Olimpiada::where('gestion', $gestion)->exists();
    }
}