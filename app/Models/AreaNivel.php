<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaNivel extends Model {
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';
    protected $fillable = ['id_area', 'id_nivel', 'activo'];
}


