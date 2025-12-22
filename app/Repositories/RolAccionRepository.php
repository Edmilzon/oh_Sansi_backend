<?php

namespace App\Repositories;

use App\Model\RolAccion;
use Illuminate\Database\Eloquent\Collection;

class RolAccionRepository
{
    public function firstOrCreate(array $busqueda, array $valores = []): RolAccion
    {
        return RolAccion::firstOrCreate($busqueda, $valores);
    }

    public function updateOrCreate(array $busqueda, array $valores = []): RolAccion
    {
        return RolAccion::updateOrCreate($busqueda, $valores);
    }

    public function getAllWithRelations(): Collection
    {
        return RolAccion::with(['accionSistema', 'rol'])->get();
    }

    public function getByRol(int $idRol): Collection
    {
        return RolAccion::with('accionSistema')
            ->where('id_rol', $idRol)
            ->get();
    }
}
