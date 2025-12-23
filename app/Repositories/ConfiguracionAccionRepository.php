<?php

namespace App\Repositories;

use App\Model\ConfiguracionAccion;
use Illuminate\Support\Collection;

class ConfiguracionAccionRepository
{
    public function getByFases(array $faseIds): Collection
    {
        return ConfiguracionAccion::with(['accionSistema', 'faseGlobal'])
            ->whereIn('id_fase_global', $faseIds)
            ->get();
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return ConfiguracionAccion::firstOrCreate($attributes, $values);
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return ConfiguracionAccion::updateOrCreate($attributes, $values);
    }

    public function updateStatus(int $id, bool $estado): void
    {
        ConfiguracionAccion::where('id_configuracion_accion', $id)
            ->update(['habilitada' => $estado ? 1 : 0]);
    }
}
