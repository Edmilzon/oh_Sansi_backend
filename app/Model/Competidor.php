<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competidor extends Model
{
    use HasFactory;

    protected $table = 'competidor';
    protected $primaryKey = 'id_competidor';

    protected $fillable = [
        'departamento',
        'contacto_tutor',
        'id_institucion',
        'id_nivel_grado', // Solo este campo
        'id_archivo_csv',
        'id_persona',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function nivelGrado()
    {
        return $this->belongsTo(NivelGrado::class, 'id_nivel_grado');
    }

    public function archivoCSV()
    {
        return $this->belongsTo(ArchivoCSV::class, 'id_archivo_csv');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_competidor');
    }

    public function registrosNota()
    {
        return $this->hasMany(RegistroNota::class, 'id_competidor');
    }

    public function grupoCompetidores()
    {
        return $this->hasMany(GrupoCompetidor::class, 'id_competidor');
    }

    public function desclasificaciones()
    {
        return $this->hasMany(Desclasificacion::class, 'id_competidor');
    }

    public function medalleros()
    {
        return $this->hasMany(Medallero::class, 'id_competidor');
    }

    public function getAreaNivelAttribute()
    {
        return $this->nivelGrado->areaNivel ?? null;
    }

    public function getGradoEscolaridadAttribute()
    {
        return $this->nivelGrado->gradoEscolaridad ?? null;
    }

    public function getAreaAttribute()
    {
        return $this->areaNivel->area ?? null;
    }

    public function getNivelAttribute()
    {
        return $this->areaNivel->nivel ?? null;
    }

    public function getOlimpiadaAttribute()
    {
        return $this->areaNivel->olimpiada ?? null;
    }
}