<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model {
    use HasFactory;

    protected $table = 'responsable_area';         
    protected $primaryKey = 'id_responsableArea';  

    protected $fillable = [
        'id_usuario',
        'id_area_nivel'
    ];
    
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel');
    }
}

