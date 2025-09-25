<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluador extends Model
{
    use HasFactory;

    protected $table = 'evaluador';
    protected $primaryKey = 'id_evaluador';

    protected $fillable = [
        'activo',
        'id_persona',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function persona() {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}