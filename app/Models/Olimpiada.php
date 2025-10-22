<?php

<<<<<<< HEAD
namespace App\Models; // Añadir el namespace
=======
namespace App\Models;
>>>>>>> 7875b2d4133b40c8e8b2e009bacd4fc7f4ce860a

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
class Olimpiada extends Model {
    use HasFactory;
    
    protected $table = 'olimpiadas';
    protected $primaryKey = 'id_olimpiada';
=======
class Olimpiada extends Model
{
    use HasFactory;

    protected $table = 'olimpiadas';
    protected $primaryKey = 'id_olimpiada';
    
>>>>>>> 7875b2d4133b40c8e8b2e009bacd4fc7f4ce860a
    protected $fillable = [
        'nombre',
        'gestion'
    ];
<<<<<<< HEAD
    
=======

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_olimpiada', 'id_olimpiada', 'id_area')
                    ->withTimestamps();
    }

    public function scopePorGestion($query, $gestion)
    {
        return $query->where('gestion', $gestion)->first();
    }

>>>>>>> 7875b2d4133b40c8e8b2e009bacd4fc7f4ce860a
}