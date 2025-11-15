<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoEntrega extends Model
{
    use HasFactory;

    protected $table = 'metodos_entrega';

    // No necesitamos timestamps (created_at) para esta tabla
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'costo',
        'descripcion',
    ];

    /**
     * Un método de entrega puede estar en muchos pedidos
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'metodo_entrega_id');
    }
}
