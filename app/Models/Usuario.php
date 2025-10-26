<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dpi',             // <-- AÑADIR
        'nit',             // <-- AÑADIR
        'foto_perfil_url', // <-- AÑADIR
        'correo',
        'telefono',
        'contrasena_hash',
        'estado',
        'pais_id',          // <-- AÑADIDO
        'departamento_id',  // <-- AÑADIDO
        'municipio_id',     // <-- AÑADIDO
        'aldea_id',         // <-- AÑADIDO
        'direccion',        // <-- AÑADIDO
    ];

    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }
}
