<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CodigoEncargado; // Importar el modelo CodigoEncargado (asumiendo que existe)

class Area extends Model {
    use HasFactory;

    protected $table = 'areas';
    protected $primaryKey = 'id_area';
    protected $fillable = ['nombre'];
    
    protected $hidden = ['pivot'];

    public function codigoEncargado() {
        return $this->hasOne(CodigoEncargado::class, 'id_area', 'id_area');
    }

    public function areaNiveles(){
        return $this->hasMany(AreaNivel::class, 'id_area');
    }
<<<<<<< HEAD
}
=======

    public function olimpiadas() {
        return $this->belongsToMany(Olimpiada::class, 'area_olimpiada', 'id_area', 'id_olimpiada')
                    ->withTimestamps();
    }

    public function scopeDeOlimpiada($query, $idOlimpiada) {
        return $query->whereHas('olimpiadas', function($q) use ($idOlimpiada) {
            $q->where('id_olimpiada', $idOlimpiada);
        });
    }
}
>>>>>>> 7875b2d4133b40c8e8b2e009bacd4fc7f4ce860a
