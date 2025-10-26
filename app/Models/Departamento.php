<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamentos';

    // Si tu tabla 'departamentos' NO tiene 'created_at'/'updated_at',
    // descomenta la siguiente línea:
    // public $timestamps = false;

    // Define la relación inversa: Un Departamento pertenece a un País
    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    // Define la relación: Un Departamento tiene muchos Municipios
    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
}
