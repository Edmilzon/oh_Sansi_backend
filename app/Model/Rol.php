<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model {
    use HasFactory;
    
    protected $table = 'rol';
    protected $primaryKey = 'id_rol'; 
    protected $fillable = [
        'nombre'
    ];

    /**
     * Relación directa con las Acciones del Sistema.
     * Permite usar: $rol->acciones
     */
    public function acciones(): BelongsToMany
    {
        return $this->belongsToMany(
            AccionSistema::class, 
            'rol_acciones', // Tabla pivote
            'id_rol',       // FK en pivote para este modelo
            'id_accion'     // FK en pivote para el otro modelo
        )
        ->withPivot('activo') // Para acceder a campos extra de la pivote
        ->withTimestamps();
    }
    
    /**
     * Relación con la tabla intermedia explícita (útil para lógica compleja).
     * Permite usar: $rol->asignaciones
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(RolAccion::class, 'id_rol', 'id_rol');
    }
}