<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';
    public $timestamps = true;

    protected $fillable = [
        'id_competidor',
        'id_examen',
        'nota',
        'estado_participacion',
        'observacion',
        'resultado_calculado',
        'bloqueado_por',
        'fecha_bloqueo',
        'esta_calificado',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
        'fecha_bloqueo' => 'datetime',
        'esta_calificado' => 'boolean',
    ];

    /**
     * Examen al que pertenece esta evaluación.
     */
    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class, 'id_examen', 'id_examen');
    }

    /**
     * Competidor evaluado.
     */
    public function competidor(): BelongsTo
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    /**
     * Juez que tiene bloqueada la ficha (si aplica).
     */
    public function usuarioBloqueo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'bloqueado_por', 'id_usuario');
    }

    /**
     * Historial de cambios de nota (Auditoría).
     */
    public function logsCambios(): HasMany
    {
        return $this->hasMany(LogCambioNota::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function getNombreJuezBloqueoAttribute(): ?string
    {
        if (!$this->bloqueado_por) {
            return null;
        }

        $usuario = $this->usuarioBloqueo;

        if ($usuario && $usuario->persona) {
            return $usuario->persona->nombre . ' ' . $usuario->persona->apellido;
        }

        return 'Juez (ID: ' . $this->bloqueado_por . ')';
    }

    /**
     * Verifica si la evaluación está "Zombie" (Bloqueada hace mucho tiempo).
     * Uso: $evaluacion->es_zombie
     */
    public function getEsZombieAttribute(): bool
    {
        if (!$this->fecha_bloqueo) {
            return false;
        }

        return $this->fecha_bloqueo->diffInMinutes(now()) > 10;
    }
}
