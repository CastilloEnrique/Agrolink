<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    /**
     * Le decimos a Eloquent que esta tabla solo tiene 'created_at', pero no 'updated_at'.
     */
    const UPDATED_AT = null;

    /**
     * Campos adaptados a tu tabla 'pedidos'.
     */
    protected $fillable = [
        'usuario_id',
        'metodo_entrega_id',
        'direccion_entrega',
        'estado',
        'precio_total',
    ];

    /**
     * Un pedido pertenece a un usuario (Consumidor).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Un pedido tiene muchos items (detalles).
     * 💡 Apunta a tu modelo 'PedidoItem'.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}

