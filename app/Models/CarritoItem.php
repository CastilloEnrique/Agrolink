<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $fillable = [
        'usuario_id',
        'producto_id',
        'cantidad',
    ];

    /**
     * Relación: Un item del carrito pertenece a un producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación: Un item del carrito pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
