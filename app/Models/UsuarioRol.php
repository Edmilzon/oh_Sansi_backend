<?php

use App\Models\Roles;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model {
    use HasFactory;
    
    protected $table = 'usuario_rol';
    protected $primaryKey = 'id_ususario_rol';
    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_olimpiada'
    ];
    
    public function usuario(){
        return $this->belongsTo(Usuario::class,'id_usuario');
    }
    
    public function rol(){
        return $this->belongsTo(Roles::class, 'id_rol');
    }
}