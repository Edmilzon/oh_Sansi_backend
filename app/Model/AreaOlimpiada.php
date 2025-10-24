<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaOlimpiada extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'area_olimpiada';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_area_olimpiada';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id_area', 'id_olimpiada'];

    /**
     * Obtiene la olimpiada a la que pertenece esta área.
     */
    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }
}
