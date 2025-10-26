<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'pais'; // <-- Indica el nombre correcto de tu tabla

    // Si tu tabla 'pais' NO tiene las columnas 'created_at' y 'updated_at',
    // descomenta la siguiente línea:
    // public $timestamps = false;

    // Define la relación: Un País tiene muchos Departamentos
    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }
}
