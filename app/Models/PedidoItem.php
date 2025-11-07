<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 💡 Este modelo es para tu tabla 'pedido_items'
 */
class PedidoItem extends Model
{
    use HasFactory;

    /**
     * Especificamos el nombre de tu tabla
     */
    protected $table = 'pedido_items';

    /**
     * Le decimos a Eloquent que esta tabla no tiene 'created_at' ni 'updated_at'.
     */
    public $timestamps = false;

    /**
     * Campos adaptados a tu tabla 'pedido_items' (con las 2 columnas añadidas).
     */
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'productor_id', // 💡 Columna añadida
        'cantidad',
        'precio_unitario',
        'subtotal',     // 💡 Columna añadida
    ];

    /**
     * El item pertenece a un producto.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
