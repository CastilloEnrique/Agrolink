<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenProducto extends Model
{
    // 🔹 Nombre de la tabla en la base de datos
    protected $table = 'imagenes_producto';

    // 🔹 Campos que pueden asignarse masivamente
    protected $fillable = [
        'producto_id',
        'ruta_imagen',
        'principal'
    ];

    /**
     * 🔸 Relación: una imagen pertenece a un producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * 🔸 Accesor personalizado:
     * Devuelve la URL pública de la imagen almacenada en storage/app/public/productos/
     * Ejemplo de salida: http://127.0.0.1:8000/storage/productos/maiz.jpg
     */
    public function getUrlPublicaAttribute()
    {
        return asset('storage/' . ltrim($this->ruta_imagen, '/'));
    }
}
