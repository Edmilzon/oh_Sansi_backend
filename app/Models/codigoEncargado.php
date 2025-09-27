<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoEncargado extends Model
{
    protected $table = 'codigo_encargado';
    protected $primaryKey = 'id_codigo_encargado';
    protected $fillable = ['codigo', 'descripcion', 'id_area'];
}