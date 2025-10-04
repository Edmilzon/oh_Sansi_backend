<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model {

    use HasFactory;

    protected $table = 'nivel';

    protected $primaryKey = 'id_nivel';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden'
        ];

    public function areaNiveles(){
        return $this->hasMany(AreaNivel::class, 'id_nivel');
    }

}