<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilProductor extends Model
{
    protected $table = 'perfiles_productores';
    protected $fillable = [
        'usuario_id',
        'aldea_id',
        'whatsapp',
        'direccion',
        'ubicacion_lat',
        'ubicacion_lng',
        'estado_validacion',
        'observaciones',
    ];

    // Relación inversa: Un perfil pertenece a un Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación con Aldea para ubicación
    public function aldea()
    {
        return $this->belongsTo(Aldea::class, 'aldea_id');
    }
}
