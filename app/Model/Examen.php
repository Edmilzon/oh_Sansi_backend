<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    use HasFactory;

    protected $table = 'examen';
    protected $primaryKey = 'id_examen';

    protected $fillable = [
        'id_competencia',
        'nombre',
        'ponderacion',
        'maxima_nota',
        'fecha_hora_inicio',
        'tipo_regla',
        'configuracion_reglas',
        'estado_ejecucion',
        'fecha_inicio_real',
    ];

    protected $casts = [
        'ponderacion' => 'decimal:2',
        'maxima_nota' => 'decimal:2',
        'fecha_hora_inicio' => 'datetime',
        'fecha_inicio_real' => 'datetime',
        'configuracion_reglas' => 'array',
    ];

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'id_examen', 'id_examen');
    }
}
