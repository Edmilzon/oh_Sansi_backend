<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory;

    protected $table = 'competencia';
    protected $primaryKey = 'id_competencia';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_responsableArea',
        'id_fase',
        'id_parametro',
        'id_evaluacion',
    ];

    public function responsableArea()
    {
        return $this->belongsTo(ResponsableArea::class, 'id_responsableArea', 'id_responsableArea');
    }

    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluadorAN', 'id_evaluadorAN');
    }

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    public function fase()
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    public function parametro()
    {
        return $this->belongsTo(Parametro::class, 'id_parametro', 'id_parametro');
    }

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    /**
     * Get the medallero for the competencia.
     */
    public function medallero()
    {
        return $this->hasMany(Medallero::class, 'id_competencia', 'id_competencia');
    }
}
