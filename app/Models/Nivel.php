<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model {

    use HasFactory;

    protected $table = 'niveles';

    protected $primaryKey = 'id_nivel';

    protected $fillable = [
        'nombre'
        ];

    public function areaNiveles(){
        return $this->hasMany(AreaNivel::class, 'id_nivel');
    }

}