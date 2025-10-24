<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model {
    use HasFactory;
    
    protected $table = 'olimpiada';
    protected $primaryKey = 'id_olimpiada';
    protected $fillable = [
        'nombre',
        'gestion'
    ];
    
}