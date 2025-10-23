<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoCsv extends Model {
    use HasFactory;

    protected $table = 'archivo_csv';
    protected $primaryKey = 'id_archivo_csv';
    protected $fillable = [
        'nombre',
        'fecha_subida'];

    /*public function competidores() {
        return $this->hasMany(Competidor::class, 'id_archivo_csv', 'id_archivo_csv');
    }*/
}


