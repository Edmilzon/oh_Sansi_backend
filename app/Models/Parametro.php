<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'parametro';
    protected $primaryKey = 'id_parametro';

    protected $fillable = [
        'nota_min_clasif',
        'max_oros',
        'max_platas',
        'max_bronces',
        'max_menciones',
        'id_competencia'
    ];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }
}