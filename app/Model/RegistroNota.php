<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroNota extends Model
{
    use HasFactory;

    protected $table = 'registro_nota';
    protected $primaryKey = 'id_registro_nota';

    protected $fillable = [
        'id_area_nivel',
        'id_evaluadorAN',
        'id_competidor',
        'accion',
        'nota_anterior',
        'nota_nueva',
        'observacion',
        'descripcion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(\App\Model\AreaNivel::class, 'id_area_nivel');
    }
    
    public function evaluadorAN()
    {
        return $this->belongsTo(\App\Model\EvaluadorAreaNivel::class, 'id_evaluadorAN');
    }
    
    public function competidor()
    {
        return $this->belongsTo(\App\Model\Competidor::class, 'id_competidor');
    }

     public function getNombreEvaluadorAttribute()
    {
        if ($this->evaluador && $this->evaluador->usuario) {
            return $this->evaluador->usuario->nombre . ' ' . $this->evaluador->usuario->apellido;
        }
        return 'N/A';
    }

    public function getNombreOlimpistaAttribute()
    {
        if ($this->competidor && $this->competidor->persona) {
            return $this->competidor->persona->nombre . ' ' . $this->competidor->persona->apellido;
        }
        return 'N/A';
    }

    public function getAreaAttribute()
    {
        return $this->areaNivel->area->nombre ?? 'N/A';
    }

    public function getNivelAttribute()
    {
        return $this->areaNivel->nivel->nombre ?? 'N/A';
    }

    public function getIdAreaAttribute()
    {
        return $this->areaNivel->area->id_area ?? null;
    }

    public function getIdNivelAttribute()
    {
        return $this->areaNivel->nivel->id_nivel ?? null;
    }

    public function getFechaHoraAttribute()
    {
        return $this->created_at->toISOString();
    }
}