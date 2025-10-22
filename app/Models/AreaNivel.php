<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area; // Importar el modelo Area
use App\Models\Nivel; // Importar el modelo Nivel
use App\Models\Olimpiada; // Importar el modelo Olimpiada

class AreaNivel extends Model {
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';
    protected $fillable = [
        'id_area',
        'id_nivel',
        'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];
    public function area() {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function nivel() {
        return $this->belongsTo(Nivel::class, 'id_nivel');
    }

    public function olimpiada() {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }
}
