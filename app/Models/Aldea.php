<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aldea extends Model
{
    use HasFactory;

    protected $table = 'aldeas';

    // Si tu tabla 'aldeas' NO tiene 'created_at'/'updated_at',
    // descomenta la siguiente línea:
    // public $timestamps = false;

    // Define la relación inversa: Una Aldea pertenece a un Municipio
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
}
