<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    /**
     * El nombre de la tabla en la base de datos.
     * (Asumiendo que tu tabla se llama 'pedido_detalles' o 'pedido_items')
     */
    protected $table = 'pedido_items'; // 💡 Revisa que este sea el nombre de tu tabla

    /**
     * Campos que se pueden llenar masivamente.
     */
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'productor_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    /**
     * 💡 RELACIÓN FALTANTE:
     * Un item del detalle pertenece a UN pedido principal.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * 💡 RELACIÓN (Que ya debías tener para la otra vista):
     * Un item del detalle pertenece a UN producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Opcional: Relación con el productor (vendedor)
     */
    public function productor()
    {
        return $this->belongsTo(Usuario::class, 'productor_id');
    }
}
