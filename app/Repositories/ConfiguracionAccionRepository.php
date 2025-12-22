<?php

namespace App\Repositories;

use App\Model\ConfiguracionAccion;
use Illuminate\Database\Eloquent\Collection;

class ConfiguracionAccionRepository
{
    public function updateOrCreate(array $busqueda, array $datos): ConfiguracionAccion
    {
        return ConfiguracionAccion::updateOrCreate($busqueda, $datos);
    }

    public function firstOrCreate(array $busqueda, array $datos): ConfiguracionAccion
    {
        return ConfiguracionAccion::firstOrCreate($busqueda, $datos);
    }
    
    public function getByFases(array $faseIds): Collection
    {
        return ConfiguracionAccion::with(['accionSistema', 'faseGlobal'])
            ->whereIn('id_fase_global', $faseIds)
            ->get();
    }
}
