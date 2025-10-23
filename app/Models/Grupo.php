<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'max_integrantes',
    ];
    
    public function grupocompetidor() {
        return $this->hasMany(GrupoCompetidor::class, 'id_grupo', 'id_grupo');
    }
}