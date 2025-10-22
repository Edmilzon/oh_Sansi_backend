<?php

namespace App\Models; // Añadir el namespace

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model {
    use HasFactory;
    
    protected $table = 'olimpiadas';
    protected $primaryKey = 'id_olimpiada';
    protected $fillable = [
        'nombre',
        'gestion'
    ];
    
}