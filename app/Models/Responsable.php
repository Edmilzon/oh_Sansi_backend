<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Persona;

class Responsable extends Model {
    use HasFactory;

    protected $table = 'responsable_area';         
    protected $primaryKey = 'id_responsable_area';  
    protected $fillable = ['fecha_asignacion','activo','id_persona','id_area'];

    public function persona() {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}

