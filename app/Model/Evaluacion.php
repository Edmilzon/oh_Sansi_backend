<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';

    protected $fillable = [
        'nota',
        'observaciones',
        'fecha_evaluacion',
        'estado',
        'id_evaluadorAN',
        'id_competidor',
    ];

    /**
     * Get the evaluadorAn that owns the evaluacion.
     */
    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluadorAN', 'id_evaluadorAN');
    }

    /**
     * Get the competidor that owns the evaluacion.
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }
}
