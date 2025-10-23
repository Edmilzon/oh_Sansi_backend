<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model
{
    use HasFactory;

    protected $table = 'olimpiadas';
    protected $primaryKey = 'id_olimpiada';
    
    protected $fillable = [
        'nombre',
        'gestion'
    ];

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_olimpiada', 'id_olimpiada', 'id_area')
                    ->withTimestamps();
    }

    public function scopePorGestion($query, $gestion)
    {
        return $query->where('gestion', $gestion)->first();
    }

}