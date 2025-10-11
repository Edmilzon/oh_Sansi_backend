<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competidor extends Model
{
    use HasFactory;

    protected $table = 'competidor';
    protected $primaryKey = 'id_competidor';

    protected $fillable = [
        'grado_escolar',
        'departamento',
        'nombre_tutor',
        'contacto_tutor',
        'contacto_emergencia',
        'id_persona',
        'id_institucion',
        'id_area',
        'id_nivel',
        /*'id_archivo_csv'*/
    ];

    public function persona() {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function institucion() {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }
    public function area() {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }
    public function nivel() {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function grupocompetidor() {
        return $this->hasMany(GrupoCompetidor::class, 'id_competidor', 'id_competidor');
    }

    /*public function archivoCsv() {
        return $this->belongsTo(ArchivoCsv::class, 'id_archivo_csv', 'id_archivo_csv');
    }*/
}