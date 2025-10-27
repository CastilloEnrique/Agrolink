<?php
//
//
//namespace App\Http\Controllers;
//
//
//
//    use Illuminate\Http\Request;
//    use App\Models\Usuario;
//    use Illuminate\Support\Facades\Hash;
//    use Illuminate\Validation\Rules\Password;
//
//    // 👈 Asegúrate de tener este
//    use Illuminate\Support\Facades\Storage;
//
//    // 👈 Asegúrate de tener este
//    use Illuminate\Support\Facades\Log;
//
//    // 👈 Asegúrate de tener este
//
//    class AuthController extends Controller
//    {
//        /**
//         * =================================================================
//         * MÉTODO DE REGISTRO (ACTUALIZADO CON TODO)
//         * =================================================================
//         */
//        public function register(Request $request)
//        {
//            // 1. Validación completa
//            $validatedData = $request->validate([
//                'primer_nombre' => 'required|string|max:100',
//                'segundo_nombre' => 'nullable|string|max:100', // <-- Nuevo
//                'primer_apellido' => 'required|string|max:100',
//                'segundo_apellido' => 'nullable|string|max:100', // <-- Nuevo
//                'dpi' => 'nullable|string|max:20|unique:usuarios,dpi',
//                'nit' => 'nullable|string|max:15|unique:usuarios,nit',
//                'correo' => 'required|string|email|max:150|unique:usuarios,correo',
//                'password' => ['required', 'confirmed', Password::min(8)],
//                'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Añadí webp por si acaso
//                 'pais_id' => 'nullable|integer|exists:paises,id', // Descomenta si usas pais_id
//                'departamento_id' => 'nullable|integer|exists:departamentos,id',
//                'municipio_id' => 'nullable|integer|exists:municipios,id',
//                'aldea_id' => 'nullable|integer|exists:aldeas,id', // Tu regla: "puede ir nulla"
//                'direccion' => 'nullable|string|max:255',
//
//            ]);
//
//            $fotoUrl = null;
//
//            // 2. Lógica para subir la foto
//            try {
//                if ($request->hasFile('foto')) {
//                    // Guarda en 'storage/app/public/fotos_perfil'
//                    $path = $request->file('foto')->store('public/fotos_perfil');
//                    // Obtiene la URL pública (ej: /storage/fotos_perfil/archivo.jpg)
//                    $fotoUrl = Storage::url($path);
//                }
//            } catch (\Exception $e) {
//                Log::error('Error al subir foto de perfil: ' . $e->getMessage());
//            }
//
//            // 3. Crear el Usuario en la Base de Datos
//            $usuario = Usuario::create([
//                'primer_nombre' => $validatedData['primer_nombre'],
//                'segundo_nombre' => $validatedData['segundo_nombre'] ?? null, // <-- Nuevo
//                'primer_apellido' => $validatedData['primer_apellido'],
//                'segundo_apellido' => $validatedData['segundo_apellido'] ?? null, // <-- Nuevo
//                'dpi' => $validatedData['dpi'] ?? null,
//                'nit' => $validatedData['nit'] ?? null,
//                'correo' => $validatedData['correo'],
//                'contrasena_hash' => Hash::make($validatedData['password']),
//                'foto_perfil_url' => $fotoUrl,
//                'pais_id' => $validatedData['pais_id'] ?? null,
//                'departamento_id' => $validatedData['departamento_id'] ?? null,
//                'municipio_id' => $validatedData['municipio_id'] ?? null,
//                'aldea_id' => $validatedData['aldea_id'] ?? null,       // <-- Nuevo
//                'direccion' => $validatedData['direccion'] ?? null,     // <-- Nuevo
//                'estado' => 'activo', // O 'pendiente_validacion'
//            ]);
//
//            // 4. Iniciar sesión automáticamente (crear token)
//            $token = $usuario->createToken('auth_token')->plainTextToken;
//
//            // 5. Enviar respuesta al frontend
//            return response()->json([
//                'access_token' => $token,
//                'usuario' => $usuario,
//            ], 201); // 201 = Recurso Creado
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
//         * MÉTODO DE LOGOUT (Tu código original)
//         * =================================================================
//         */
//        public function logout(Request $request)
//        {
//            // Asegúrate de que solo los usuarios autenticados puedan hacer logout
//            if ($request->user()) {
//                $request->user()->tokens()->delete();
//                return response()->json(['message' => 'Sesión cerrada correctamente']);
//            }
//            return response()->json(['message' => 'No autenticado'], 401);
//        }
//    }
//
//
//




    namespace App\Http\Controllers;

    use App\Models\PerfilProductor;
    use Illuminate\Http\Request;
    use App\Models\Usuario;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\Rules\Password;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Log;
    use App\Models\Aldea; // 👈 ¡Importa el modelo Aldea!
    use Illuminate\Validation\ValidationException; // Para errores específicos
    use App\Models\Rol;

    class AuthController extends Controller
    {
        /**
         * =================================================================
         * MÉTODO DE REGISTRO COMPLETO
         * =================================================================
         *
         * Valida y guarda todos los campos del nuevo formulario,
         * incluyendo la foto de perfil y los IDs de geografía.
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
                'password' => ['required', 'confirmed', Password::min(8)],
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
            } catch (\Exception $e) { Log::error('Error al subir foto: ' . $e->getMessage()); }


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
         * MÉTODO DE LOGIN (Tu código original)
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
         * MÉTODO DE LOGOUT (Corregido para cerrar sesión actual)
         * =================================================================
         */
        public function logout(Request $request)
        {
            // Asegúrate de que el usuario esté autenticado
            if ($request->user()) {
                // Cierra solo la sesión actual (el token usado para esta petición)
                $request->user()->currentAccessToken()->delete();
                return response()->json(['message' => 'Sesión cerrada correctamente']);
            }
            return response()->json(['message' => 'No autenticado'], 401);
        }
    }
