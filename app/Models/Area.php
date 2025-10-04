<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model {
    use HasFactory;

    protected $table = 'area';
    protected $primaryKey = 'id_area';
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function codigoEncargado() {
        return $this->hasOne(CodigoEncargado::class, 'id_area', 'id_area');
    }

    public function areaNiveles(){
        return $this->hasMany(AreaNivel::class, 'id_area');
    }
}


