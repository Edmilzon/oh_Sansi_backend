<?php

namespace App\Services;

use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\OlimpiadaRepository;

class OlimpiadaService
{
    protected $oimpiadaRepository;

    public function __construct(OlimpiadaRepository $oimpiadaRepository)
    {
        $this->OlimpiadaRepository = $oimpiadaRepository;
    }

    public function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => $gestionActual],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function obtenerOlimpiadaPorGestion($gestion): Olimpiada
    {
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestion";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => $gestion],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function existeOlimpiadaActual(): bool
    {
        $gestionActual = date('Y');
        return Olimpiada::where('gestion', $gestionActual)->exists();
    }

    public function obtenerOlimpiadasAnteriores(): Collection
    {
        $gestionActual = date('Y');
        return $this->OlimpiadaRepository->obtenerOlimpiadasAnteriores($gestionActual);
    }
}