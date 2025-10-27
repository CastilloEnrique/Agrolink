<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = [
        'usuario_id', 'categoria_id', 'nombre', 'descripcion',
        'precio_referencia', 'unidad_medida', 'stock_actual',
        'disponibilidad', 'estado_publicacion', 'fecha_cosecha'
    ];
    public function user()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }
}
