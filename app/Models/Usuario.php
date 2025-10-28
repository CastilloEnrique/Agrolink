<?php
//
//namespace App\Models;
//
//use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
//
//class Usuario extends Authenticatable
//{
//    use HasApiTokens, Notifiable;
//
//    protected $table = 'usuarios';
//    protected $primaryKey = 'id';
//    public $timestamps = true;
//
//    protected $fillable = [
//        'primer_nombre',
//        'segundo_nombre',
//        'primer_apellido',
//        'segundo_apellido',
//        'dpi',
//        'fecha_nacimiento',
//        'nit',             // <-- AÑADIR
//        'foto_perfil_url', // <-- AÑADIR
//        'correo',
//        'telefono',
//        'contrasena_hash',
//        'estado',
////        'pais_id',          // <-- AÑADIDO
//        'departamento_id',  // <-- AÑADIDO
//        'municipio_id',     // <-- AÑADIDO
//        'aldea_id',         // <-- AÑADIDO
//        'direccion',        // <-- AÑADIDO
//    ];
//
//    protected $hidden = [
//        'contrasena_hash',
//        'remember_token',
//    ];
//
//    public function getAuthPassword()
//    {
//        return $this->contrasena_hash;
//    }
//
//    public function getEmailForPasswordReset()
//    {
//        return $this->correo;
//    }
//
//    public function roles()
//    {
//        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
//    }
//    // En app/Models/Usuario.php
//    public function perfilProductor()
//    {
//        // Un usuario tiene un perfil de productor (relación uno a uno)
//        return $this->hasOne(PerfilProductor::class, 'usuario_id');
//    }
//}



namespace App\Models; // 💡 1. CAMBIO CRÍTICO: Debe ser 'App\Models'

// 💡 2. Asegúrate de que los otros modelos también estén en 'App\Models'
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Aldea;
use App\Models\Rol;
use App\Models\PerfilProductor;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// 💡 3. Asumimos que tu clase se llama 'Usuario'
class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * El nombre de la tabla asociada con el modelo.
     */
    protected $table = 'usuarios'; // 💡 Asegúrate que este sea el nombre de tu tabla

    /**
     * El nombre de la columna de la clave primaria.
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe tener timestamps.
     */
    public $timestamps = true;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dpi',
        'fecha_nacimiento',
        'nit',
        'correo',
        'contrasena_hash', // 💡 Asegúrate que coincida con tu BD
        'foto_perfil_url',
        'pais_id',
        'departamento_id',
        'municipio_id',
        'aldea_id',
        'direccion',
        'estado',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     */
    protected $hidden = [
        'contrasena_hash', // 💡 Asegúrate que coincida con tu BD
        'remember_token',
    ];

    /**
     * Los atributos que deben ser casteados.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
    ];

    // --- 💡 RELACIONES (Asegúrate que los modelos existan) ---

    /**
     * Relación con la tabla Paises (para pais_id)
     */
    public function pais(): BelongsTo
    {
        // 💡 4. Asegúrate que 'Pais' existe en 'App\Models\Pais'
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    /**
     * Relación con la tabla Departamentos (para departamento_id)
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    /**
     * Relación con la tabla Municipios (para municipio_id)
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    /**
     * Relación con la tabla Aldeas (para aldea_id)
     */
    public function aldea(): BelongsTo
    {
        return $this->belongsTo(Aldea::class, 'aldea_id');
    }

    /**
     * Relación con PerfilProductor
     */
    public function perfilProductor(): HasOne
    {
        return $this->hasOne(PerfilProductor::class, 'usuario_id');
    }

    /**
     * Relación muchos a muchos con Roles
     */
    public function roles(): BelongsToMany
    {
        // Asegúrate que los nombres de tabla y columnas pivot sean correctos
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }

    // --- FIN DE RELACIONES ---
}

