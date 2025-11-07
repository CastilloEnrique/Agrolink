<?php
//
//
//
//
//
//    namespace App\Http\Controllers;
//    use Illuminate\Support\Facades\Password;
//    use App\Models\PerfilProductor;
//    use Illuminate\Http\Request;
//    use App\Models\Usuario;
//    use Illuminate\Support\Facades\Hash;
//    use Illuminate\Support\Facades\Storage;
//    use Illuminate\Support\Facades\Log;
//    use App\Models\Aldea; // 👈 ¡Importa el modelo Aldea!
//    use Illuminate\Validation\ValidationException; // Para errores específicos
//    use App\Models\Rol;
//    use Illuminate\Validation\Rules\Password as PasswordRules; // 👈 Importar
//
//    class AuthController extends Controller
//    {
//        /**
//         * =================================================================
//         * MÉTODO DE REGISTRO COMPLETO
//         * =================================================================
//         *
//         * Valida y guarda todos los campos del nuevo formulario,
//         * incluyendo la foto de perfil y los IDs de geografía.
//         */
//        public function register(Request $request)
//        {
//            // 1. Validación de TODOS los campos del FormData
//            $validatedData = $request->validate([
//                'primer_nombre' => 'required|string|max:100',
//                'segundo_nombre' => 'nullable|string|max:100',
//                'primer_apellido' => 'required|string|max:100',
//                'segundo_apellido' => 'nullable|string|max:100',
//                'dpi' => 'nullable|string|max:20|unique:usuarios,dpi',
//                'fecha_nacimiento' => 'nullable|date|before:today',
//                'nit' => 'nullable|string|max:15|unique:usuarios,nit',
//                'correo' => 'required|string|email|max:150|unique:usuarios,correo',
//                'password' => ['required', 'confirmed', Password::min(8)],
//                'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
//
//                // Rol y Geografía
//                'rol_elegido' => 'required|string|in:Productor,Consumidor,Intermediario', // <-- CLAVE: Rol
//                'pais_id' => 'required|integer|exists:pais,id',
//                'departamento_id' => 'required|integer|exists:departamentos,id',
//                'municipio_id' => 'required|integer|exists:municipios,id|required_with:nueva_aldea_nombre', // Necesario para crear aldea nueva
//                'aldea_id' => 'nullable|integer|exists:aldeas,id',
//                'nueva_aldea_nombre' => 'nullable|string|max:100',
//                'direccion' => 'nullable|string|max:255',
//            ]);
//
//            $fotoUrl = null;
//            $aldeaIdParaGuardar = $validatedData['aldea_id'] ?? null;
//
//
//            // --- LOGIC: Subida de Foto ---
//            try {
//                if ($request->hasFile('foto')) {
//                    $path = $request->file('foto')->store('public/fotos_perfil');
//                    $fotoUrl = Storage::url($path);
//                }
//            } catch (\Exception $e) { Log::error('Error al subir foto: ' . $e->getMessage()); }
//
//
//            // --- LOGIC: Creación de Aldea Nueva ---
//            if (!empty($validatedData['nueva_aldea_nombre'])) {
//                try {
//                    $nuevaAldea = Aldea::firstOrCreate(
//                        ['municipio_id' => $validatedData['municipio_id'], 'nombre' => ucfirst(strtolower(trim($validatedData['nueva_aldea_nombre'])))]
//                    );
//                    $aldeaIdParaGuardar = $nuevaAldea->id;
//                } catch (\Exception $e) {
//                    Log::error('Error al crear aldea: ' . $e->getMessage());
//                    throw ValidationException::withMessages(['nueva_aldea_nombre' => 'No se pudo guardar la nueva aldea.']);
//                }
//            }
//
//
//            // 3. Crear el Usuario en la Base de Datos
//            $usuario = Usuario::create([
//                'primer_nombre' => $validatedData['primer_nombre'],
//                'segundo_nombre' => $validatedData['segundo_nombre'] ?? null,
//                'primer_apellido' => $validatedData['primer_apellido'],
//                'segundo_apellido' => $validatedData['segundo_apellido'] ?? null,
//                'dpi' => $validatedData['dpi'] ?? null,
//                'fecha_nacimiento' => $validatedData['fecha_nacimiento'] ?? null,
//                'nit' => $validatedData['nit'] ?? null,
//                'correo' => $validatedData['correo'],
//                'contrasena_hash' => Hash::make($validatedData['password']),
//                'foto_perfil_url' => $fotoUrl,
//                'pais_id' => $validatedData['pais_id'],
//                'departamento_id' => $validatedData['departamento_id'],
//                'municipio_id' => $validatedData['municipio_id'],
//                'aldea_id' => $aldeaIdParaGuardar, // Usamos la aldea seleccionada/creada
//                'direccion' => $validatedData['direccion'] ?? null,
//                'estado' => 'activo',
//            ]);
//
//            // 4. LOGIC CLAVE: ASIGNAR ROL ELEGIDO Y CREAR PERFIL
//            $rolNombre = $validatedData['rol_elegido'];
//            try {
//                $rol = Rol::where('nombre', $rolNombre)->firstOrFail();
//                $usuario->roles()->attach($rol->id);
//
//                // Crear Perfil Específico (para Consumidor o Productor)
//                if ($rolNombre === 'Productor' || $rolNombre === 'Intermediario') {
//                    PerfilProductor::create(['usuario_id' => $usuario->id, 'estado_validacion' => 'PENDIENTE']);
//                }
//                // Si el rol fuera Consumidor, haríamos lo mismo con PerfilConsumidor
//
//            } catch (\Exception $e) {
//                Log::error('Fallo de Rol/Perfil en Registro: ' . $e->getMessage());
//                // Opcional: Revertir la creación del usuario para evitar huérfanos
//            }
//
//
//            // 5. Iniciar sesión y devolver respuesta
//            $token = $usuario->createToken('auth_token')->plainTextToken;
//            $usuario->load('roles'); // Asegurar que el frontend obtenga el rol recién asignado
//
//            return response()->json([
//                'access_token' => $token,
//                'usuario' => $usuario,
//            ], 201);
//        }
//
//
//        /**
//         * =================================================================
//         * MÉTODO DE LOGIN (Tu código original)
//         * =================================================================
//         */
//        public function login(Request $request)
//        {
//            $credentials = $request->validate([
//                'correo' => ['required', 'email'],
//                'password' => ['required'],
//            ]);
//
//            $usuario = Usuario::where('correo', $credentials['correo'])->first();
//
//            if (!$usuario || !Hash::check($credentials['password'], $usuario->contrasena_hash)) {
//                return response()->json(['message' => 'Credenciales incorrectas'], 401);
//            }
//
//            if ($usuario->estado !== 'activo') {
//                return response()->json(['message' => 'Usuario inactivo o bloqueado'], 403);
//            }
//
//            $token = $usuario->createToken('auth_token')->plainTextToken;
//
//            return response()->json([
//                'access_token' => $token,
//                'usuario' => $usuario,
//            ]);
//        }
//
//        /**
//         * =================================================================
//         * MÉTODO DE LOGOUT (Corregido para cerrar sesión actual)
//         * =================================================================
//         */
//        public function logout(Request $request)
//        {
//            // Asegúrate de que el usuario esté autenticado
//            if ($request->user()) {
//                // Cierra solo la sesión actual (el token usado para esta petición)
//                $request->user()->currentAccessToken()->delete();
//                return response()->json(['message' => 'Sesión cerrada correctamente']);
//            }
//            return response()->json(['message' => 'No autenticado'], 401);
//        }
//
//        /**
//         * Envía el enlace de reseteo de contraseña.
//         */
//        public function sendResetLink(Request $request)
//        {
//            $request->validate(['email' => 'required|email|exists:usuarios,correo']);
//
//            // 💡 Laravel usa el método 'getEmailForPasswordReset' que definimos
//            // en el modelo Usuario para buscar por 'correo' aunque aquí usemos 'email'.
//            $status = Password::broker()->sendResetLink(
//                $request->only('email')
//            );
//
//            if ($status == Password::RESET_LINK_SENT) {
//                return response()->json(['message' => 'Enlace de reseteo enviado.'], 200);
//            }
//
//            return response()->json(['message' => 'No se pudo enviar el enlace.'], 400);
//        }
//
//        /**
//         * Resetea la contraseña del usuario.
//         */
//        public function resetPassword(Request $request)
//        {
//            $request->validate([
//                'token' => 'required',
//                'email' => 'required|email|exists:usuarios,correo',
//                'password' => ['required', 'confirmed', PasswordRules::min(8)],
//            ]);
//
//            // 💡 Prepara las credenciales para el broker
//            $credentials = [
//                'email' => $request->email, // Esto buscará por 'correo'
//                'password' => $request->password,
//                'password_confirmation' => $request->password_confirmation,
//                'token' => $request->token
//            ];
//
//            // Intenta resetear la contraseña
//            $status = Password::broker('users')->reset($credentials, function ($user, $password) {
//                // 💡 Actualiza el campo de tu base de datos
//                $user->contrasena_hash = Hash::make($password);
//                $user->save();
//            });
//
//            if ($status == Password::PASSWORD_RESET) {
//                return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
//            }
//
//            return response()->json(['message' => 'No se pudo actualizar la contraseña (token inválido).'], 400);
//        }
//    }


namespace App\Http\Controllers;

// 💡 1. AÑADIDO
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Password;
use App\Models\PerfilProductor;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Aldea;
use Illuminate\Validation\ValidationException;
use App\Models\Rol;
use Illuminate\Validation\Rules\Password as PasswordRules;

class AuthController extends Controller
{
    /**
     * =================================================================
     * MÉTODO DE REGISTRO COMPLETO (Tu código - Sin cambios)
     * =================================================================
     */
    public function register(Request $request)
    {
        // 1. Validación de TODOS los campos del FormData
        $validatedData = $request->validate([
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'dpi' => 'nullable|string|max:20|unique:usuarios,dpi',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'nit' => 'nullable|string|max:15|unique:usuarios,nit',
            'correo' => 'required|string|email|max:150|unique:usuarios,correo',
            'password' => ['required', 'confirmed', PasswordRules::min(8)], // 💡 Corregido de Password::min a PasswordRules::min
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Rol y Geografía
            'rol_elegido' => 'required|string|in:Productor,Consumidor,Intermediario', // <-- CLAVE: Rol
            'pais_id' => 'required|integer|exists:pais,id',
            'departamento_id' => 'required|integer|exists:departamentos,id',
            'municipio_id' => 'required|integer|exists:municipios,id|required_with:nueva_aldea_nombre', // Necesario para crear aldea nueva
            'aldea_id' => 'nullable|integer|exists:aldeas,id',
            'nueva_aldea_nombre' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        $fotoUrl = null;
        $aldeaIdParaGuardar = $validatedData['aldea_id'] ?? null;


        // --- LOGIC: Subida de Foto ---
        try {
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('public/fotos_perfil');
                $fotoUrl = Storage::url($path);
            }
        } catch (\Exception $e) {
            Log::error('Error al subir foto: ' . $e->getMessage());
        }


        // --- LOGIC: Creación de Aldea Nueva ---
        if (!empty($validatedData['nueva_aldea_nombre'])) {
            try {
                $nuevaAldea = Aldea::firstOrCreate(
                    ['municipio_id' => $validatedData['municipio_id'], 'nombre' => ucfirst(strtolower(trim($validatedData['nueva_aldea_nombre'])))]
                );
                $aldeaIdParaGuardar = $nuevaAldea->id;
            } catch (\Exception $e) {
                Log::error('Error al crear aldea: ' . $e->getMessage());
                throw ValidationException::withMessages(['nueva_aldea_nombre' => 'No se pudo guardar la nueva aldea.']);
            }
        }


        // 3. Crear el Usuario en la Base de Datos
        $usuario = Usuario::create([
            'primer_nombre' => $validatedData['primer_nombre'],
            'segundo_nombre' => $validatedData['segundo_nombre'] ?? null,
            'primer_apellido' => $validatedData['primer_apellido'],
            'segundo_apellido' => $validatedData['segundo_apellido'] ?? null,
            'dpi' => $validatedData['dpi'] ?? null,
            'fecha_nacimiento' => $validatedData['fecha_nacimiento'] ?? null,
            'nit' => $validatedData['nit'] ?? null,
            'correo' => $validatedData['correo'],
            'contrasena_hash' => Hash::make($validatedData['password']),
            'foto_perfil_url' => $fotoUrl,
            'pais_id' => $validatedData['pais_id'],
            'departamento_id' => $validatedData['departamento_id'],
            'municipio_id' => $validatedData['municipio_id'],
            'aldea_id' => $aldeaIdParaGuardar, // Usamos la aldea seleccionada/creada
            'direccion' => $validatedData['direccion'] ?? null,
            'estado' => 'activo',
        ]);

        // 4. LOGIC CLAVE: ASIGNAR ROL ELEGIDO Y CREAR PERFIL
        $rolNombre = $validatedData['rol_elegido'];
        try {
            $rol = Rol::where('nombre', $rolNombre)->firstOrFail();
            $usuario->roles()->attach($rol->id);

            // Crear Perfil Específico (para Consumidor o Productor)
            if ($rolNombre === 'Productor' || $rolNombre === 'Intermediario') {
                PerfilProductor::create(['usuario_id' => $usuario->id, 'estado_validacion' => 'PENDIENTE']);
            }
            // Si el rol fuera Consumidor, haríamos lo mismo con PerfilConsumidor

        } catch (\Exception $e) {
            Log::error('Fallo de Rol/Perfil en Registro: ' . $e->getMessage());
            // Opcional: Revertir la creación del usuario para evitar huérfanos
        }


        // 5. Iniciar sesión y devolver respuesta
        $token = $usuario->createToken('auth_token')->plainTextToken;
        $usuario->load('roles'); // Asegurar que el frontend obtenga el rol recién asignado

        return response()->json([
            'access_token' => $token,
            'usuario' => $usuario,
        ], 201);
    }


    /**
     * =================================================================
     * MÉTODO DE LOGIN (Tu código - Sin cambios)
     * =================================================================
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = Usuario::where('correo', $credentials['correo'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->contrasena_hash)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if ($usuario->estado !== 'activo') {
            return response()->json(['message' => 'Usuario inactivo o bloqueado'], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'usuario' => $usuario,
        ]);
    }

    /**
     * =================================================================
     * 💡 MÉTODO DE LOGOUT (CORREGIDO)
     * =================================================================
     */

    /**
     * =================================================================
     * MÉTODO DE LOGOUT (Corregido anteriormente)
     * =================================================================
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }


    /**
     * =================================================================
     * 💡 MÉTODO DE FORGOT PASSWORD (CORREGIDO)
     * =================================================================
     */
    public function sendResetLink(Request $request)
    {
        // La validación está bien, comprueba que el valor exista en la columna 'correo'
        $request->validate(['email' => 'required|email|exists:usuarios,correo']);

        // 💡 --- AQUÍ ESTÁ EL CAMBIO ---
        // Le pasamos un array con la llave 'correo' (el nombre de tu columna)
        $status = Password::broker('usuarios')->sendResetLink(
            ['correo' => $request->email]
        );
        // 💡 --- FIN DEL CAMBIO ---

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Enlace de reseteo enviado.'], 200);
        }

        return response()->json(['message' => 'No se pudo enviar el enlace.'], 400);
    }

    /**
     * =================================================================
     * 💡 MÉTODO DE RESET PASSWORD (CORREGIDO)
     * =================================================================
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,correo', // Validación correcta
            'password' => ['required', 'confirmed', PasswordRules::min(8)],
        ]);

        // 💡 --- AQUÍ ESTÁ EL CAMBIO ---
        // Creamos las credenciales usando 'correo' (el nombre de tu columna)
        $credentials = [
            'correo' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token
        ];
        // 💡 --- FIN DEL CAMBIO ---

        $status = Password::broker('usuarios')->reset($credentials, function ($user, $password) {
            $user->contrasena_hash = Hash::make($password);
            $user->save();
        });

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
        }

        return response()->json(['message' => 'No se pudo actualizar la contraseña (token inválido).'], 400);
    }

//    public function logout(Request $request)
//    {
//        $user = $request->user();
//
//        if (!$user) {
//            return response()->json(['message' => 'No autenticado'], 401);
//        }
//
//        // 1. Invalida el token de API (si se usó uno)
//        // Comprueba si existe antes de borrar
//        if ($user->currentAccessToken()) {
//            $user->currentAccessToken()->delete();
//        }
//
//        // 2. Invalida la sesión web (ESTA ES LA CORRECCIÓN para el error 500)
//        Auth::guard('web')->logout();
//
//        // 3. Limpia y regenera la sesión
//        $request->session()->invalidate();
//        $request->session()->regenerateToken();
//
//        return response()->json(['message' => 'Sesión cerrada correctamente']);
//    }
//
//
//    /**
//     * =================================================================
//     * MÉTODO DE FORGOT PASSWORD (Tu código - Sin cambios)
//     * =================================================================
//     */
//    public function sendResetLink(Request $request)
//    {
//        $request->validate(['email' => 'required|email|exists:usuarios,correo']);
//
//        $status = Password::broker('usuarios')->sendResetLink(
//            $request->only('email')
//        );
//
//        if ($status == Password::RESET_LINK_SENT) {
//            return response()->json(['message' => 'Enlace de reseteo enviado.'], 200);
//        }
//
//        return response()->json(['message' => 'No se pudo enviar el enlace.'], 400);
//    }
//
//    /**
//     * =================================================================
//     * MÉTODO DE RESET PASSWORD (Tu código - Sin cambios)
//     * =================================================================
//     */
//    public function resetPassword(Request $request)
//    {
//        $request->validate([
//            'token' => 'required',
//            'email' => 'required|email|exists:usuarios,correo',
//            'password' => ['required', 'confirmed', PasswordRules::min(8)],
//        ]);
//
//        $credentials = [
//            'email' => $request->email,
//            'password' => $request->password,
//            'password_confirmation' => $request->password_confirmation,
//            'token' => $request->token
//        ];
//
//        $status = Password::broker('usuarios')->reset($credentials, function ($user, $password) {
//            $user->contrasena_hash = Hash::make($password);
//            $user->save();
//        });
//
//        if ($status == Password::PASSWORD_RESET) {
//            return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
//        }
//
//        return response()->json(['message' => 'No se pudo actualizar la contraseña (token inválido).'], 400);
//    }
}
