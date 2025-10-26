<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $table = 'municipios';

    // Si tu tabla 'municipios' NO tiene 'created_at'/'updated_at',
    // descomenta la siguiente línea:
    // public $timestamps = false;

    // Define la relación inversa: Un Municipio pertenece a un Departamento
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // Define la relación: Un Municipio tiene muchas Aldeas
    public function aldeas()
    {
        return $this->hasMany(Aldea::class);
    }
}
