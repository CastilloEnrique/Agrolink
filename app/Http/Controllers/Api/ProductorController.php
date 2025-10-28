<?php
//
//namespace App\Http\Controllers\Api;
//
//use App\Http\Controllers\Controller;
//use App\Models\PerfilProductor;
//use App\Models\Producto;
//use App\Models\ImagenProducto;
//use App\Models\CategoriaProducto;
//use App\Models\User; // 💡 --- IMPORTANTE: Importar el modelo User ---
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Validation\Rule; // 💡 --- IMPORTANTE: Para validar email ---
//
//class ProductorController extends Controller
//{
//    // --- FUNCIÓN QUE YA TENÍAS ---
//    public function getCategorias()
//    {
//        // Solo devuelve categorías activas y el ID/nombre
//        return CategoriaProducto::where('activo', true)
//            ->orderBy('nombre')
//            ->get(['id', 'nombre']);
//    }
//
//    // --- FUNCIÓN QUE YA TENÍAS (la de publicar producto) ---
//    public function storeProduct(Request $request)
//    {
//        $user = $request->user();
//
//        // 1. Validar datos
//        $validated = $request->validate([
//            'nombre' => 'required|string|max:150',
//            'descripcion' => 'nullable|string',
//            'precio_referencia' => 'required|numeric|min:0.01',
//            'unidad_medida' => 'nullable|string|max:50',
//            'stock_actual' => 'required|integer|min:0',
//            'categoria_id' => 'required|exists:categorias_producto,id',
//            'fecha_cosecha' => 'nullable|date',
//            'imagenes' => 'required|array|min:1',
//            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:5000',
//        ]);
//
//        $producto = null;
//
//        try {
//            DB::transaction(function () use ($request, $validated, $user, &$producto) {
//
//                // 2. Crear el Producto
//                $producto = Producto::create([
//                    'usuario_id' => $user->id,
//                    'categoria_id' => $validated['categoria_id'],
//                    'nombre' => $validated['nombre'],
//                    'descripcion' => $validated['descripcion'],
//                    'precio_referencia' => $validated['precio_referencia'],
//                    'unidad_medida' => $validated['unidad_medida'],
//                    'stock_actual' => $validated['stock_actual'],
//                    'disponibilidad' => $validated['stock_actual'] > 0 ? 'disponible' : 'agotado',
//                    'estado_publicacion' => 'pendiente_revision',
//                    'fecha_cosecha' => $validated['fecha_cosecha'] ?? null,
//                ]);
//
//                // 3. Lógica para subir MÚLTIPLES Imágenes
//                if ($request->hasFile('imagenes')) {
//                    foreach ($request->file('imagenes') as $index => $file) {
//                        $path = $file->store('public/productos');
//                        $rutaImagen = Storage::url($path);
//
//                        $producto->imagenes()->create([
//                            'ruta_imagen' => $rutaImagen,
//                            // Marcar la primera imagen (index 0) como la principal
//                            'principal' => ($index == 0),
//                        ]);
//                    }
//                }
//
//            }); // Fin de la transacción
//
//        } catch (\Exception $e) {
//            Log::error('Error al subir producto o imágenes: ' . $e->getMessage());
//            return response()->json(['message' => 'Error: Falló la subida. ' . $e->getMessage()], 500);
//        }
//
//        return response()->json([
//            'message' => 'Producto publicado y enviado a revisión exitosamente.',
//            'producto' => $producto
//        ], 201);
//    }
//
//    // --- 💡💡 FUNCIÓN DE CARGAR PERFIL MODIFICADA 💡💡 ---
//    /**
//     * Obtiene el perfil COMPLETO (datos de User + PerfilProductor)
//     */
//    public function getPerfilCompleto(Request $request)
//    {
//        $user = $request->user();
//
//        // Cargar el perfil de productor
//        $perfil = PerfilProductor::where('usuario_id', $user->id)->first();
//
//        // Combina los datos del usuario con los del perfil
//        // Los campos del perfil (whatsapp, etc.) sobreescribirán
//        // cualquier campo nulo del usuario si tienen el mismo nombre (ej. 'direccion')
//        $data = $user->toArray();
//        if ($perfil) {
//            // array_merge es perfecto para esto
//            $data = array_merge($data, $perfil->toArray());
//        }
//
//        // Quitamos la contraseña del objeto, por seguridad
//        unset($data['password']);
//
//        return response()->json($data);
//    }
//
//    // --- 💡💡 FUNCIÓN DE GUARDAR PERFIL MODIFICADA 💡💡 ---
//    /**
//     * Guarda el perfil COMPLETO (datos en User y PerfilProductor)
//     */
//    public function storePerfilCompleto(Request $request)
//    {
//        $user = $request->user();
//
//        // 1. Definir los campos para cada modelo
//        $userFields = [
//            'primer_nombre' => 'required|string|max:100',
//            'segundo_nombre' => 'nullable|string|max:100',
//            'primer_apellido' => 'required|string|max:100',
//            'segundo_apellido' => 'nullable|string|max:100',
//            'dpi' => 'nullable|string|max:20',
//            'nit' => 'nullable|string|max:20',
//            'fecha_nacimiento' => 'nullable|date',
//            // El correo es especial, debe ignorar al usuario actual al validar unicidad
//            'correo' => [
//                'required',
//                'string',
//                'email',
//                'max:255',
//                Rule::unique('users')->ignore($user->id),
//            ],
//            // Campos de ubicación que están en la tabla User
//            'pais_id' => 'nullable|exists:paises,id',
//            'departamento_id' => 'nullable|exists:departamentos,id',
//            'municipio_id' => 'nullable|exists:municipios,id',
//            'aldea_id' => 'nullable|exists:aldeas,id',
//        ];
//
//        $productorFields = [
//            'whatsapp' => 'nullable|string|max:50',
//            'direccion' => 'nullable|string|max:255',
//            'ubicacion_lat' => 'nullable|numeric|between:-90,90',
//            'ubicacion_lng' => 'nullable|numeric|between:-180,180',
//        ];
//
//        // 2. Validar TODO
//        $validatedData = $request->validate(array_merge($userFields, $productorFields));
//
//        try {
//            DB::transaction(function () use ($user, $validatedData, $userFields, $productorFields) {
//
//                // 3. Separar los datos validados
//                $userData = array_intersect_key($validatedData, $userFields);
//                $productorData = array_intersect_key($validatedData, $productorFields);
//
//                // 4. Actualizar el modelo User
//                $user->update($userData);
//
//                // 5. Actualizar o Crear el modelo PerfilProductor
//                PerfilProductor::updateOrCreate(
//                    ['usuario_id' => $user->id],
//                    $productorData
//                );
//            }); // Fin de la transacción
//
//        } catch (\Exception $e) {
//            Log::error('Error al guardar perfil completo: ' . $e->getMessage());
//            return response()->json(['message' => 'Error: Falló al guardar los datos. ' . $e->getMessage()], 500);
//        }
//
//        return response()->json([
//            'message' => 'Perfil completo guardado exitosamente.',
//        ], 200);
//    }
//
//    /**
//     * Obtiene todos los productos publicados por el usuario autenticado.
//     */
//    public function getMisProductos(Request $request)
//    {
//        $user = $request->user();
//
//        // Obtener productos del usuario
//        // Usamos 'with' para cargar el nombre de la categoría y la primera imagen
//        $productos = Producto::where('usuario_id', $user->id)
//            ->with([
//                // Cargar el nombre de la categoría
//                'categoria' => function ($query) {
//                    $query->select('id', 'nombre'); // Solo traer id y nombre
//                },
//                // Cargar solo la primera imagen (la principal)
//                'imagenes' => function ($query) {
//                    $query->where('principal', true)->limit(1);
//                }
//            ])
//            ->orderBy('created_at', 'desc') // Mostrar los más nuevos primero
//            ->get();
//
//        // Formatear la respuesta para que sea más fácil de usar en Vue
//        $productosFormateados = $productos->map(function ($producto) {
//            return [
//                'id' => $producto->id,
//                'nombre' => $producto->nombre,
//                'estado_publicacion' => $producto->estado_publicacion,
//                'precio_referencia' => (float) $producto->precio_referencia,
//                'stock_actual' => $producto->stock_actual,
//                'unidad_medida' => $producto->unidad_medida,
//                'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
//                // Obtener la URL de la primera imagen, si existe
//                'primera_imagen' => $producto->imagenes->first()->ruta_imagen ?? null,
//            ];
//        });
//
//        return response()->json($productosFormateados);
//    }
//}
//


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerfilProductor;
use App\Models\Producto;
use App\Models\ImagenProducto;
use App\Models\CategoriaProducto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Response; // 💡 Nuevo Import para PDF/Descarga limpia

class ProductorController extends Controller
{
    // --- FUNCIONES DE PRODUCTOS Y CATEGORIAS ---
    public function getCategorias()
    {
        return CategoriaProducto::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function storeProduct(Request $request)
    {
        // Lógica para guardar producto (sin cambios)
        $user = $request->user();
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_referencia' => 'required|numeric|min:0.01',
            'unidad_medida' => 'nullable|string|max:50',
            'stock_actual' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'fecha_cosecha' => 'nullable|date',
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:5000',
        ]);
        $producto = null;
        try {
            DB::transaction(function () use ($request, $validated, $user, &$producto) {
                $producto = Producto::create([
                    'usuario_id' => $user->id,
                    'categoria_id' => $validated['categoria_id'],
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'],
                    'precio_referencia' => $validated['precio_referencia'],
                    'unidad_medida' => $validated['unidad_medida'],
                    'stock_actual' => $validated['stock_actual'],
                    'disponibilidad' => $validated['stock_actual'] > 0 ? 'disponible' : 'agotado',
                    'estado_publicacion' => 'pendiente_revision',
                    'fecha_cosecha' => $validated['fecha_cosecha'] ?? null,
                ]);
                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $index => $file) {
                        $path = $file->store('public/productos');
                        $rutaImagen = str_replace('public/', '', $path);

                        $producto->imagenes()->create([
                            'ruta_imagen' => $rutaImagen,
                            'principal' => ($index == 0),
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error al subir producto: ' . $e->getMessage());
            return response()->json(['message' => 'Error: Falló la subida.'], 500);
        }
        return response()->json(['message' => 'Producto publicado y enviado a revisión exitosamente.', 'producto' => $producto], 201);
    }

    // --- FUNCIÓN DE LISTAR PRODUCTOS (Mis Productos) ---
    public function getMisProductos(Request $request)
    {
        $user = $request->user();
        $productos = Producto::where('usuario_id', $user->id)
            ->with(['categoria:id,nombre', 'imagenes' => function ($query) {
                $query->orderBy('principal', 'desc')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $productosFormateados = $productos->map(function ($producto) {
            $imagen = $producto->imagenes->first();
            $rutaImagen = $imagen ? $imagen->ruta_imagen : null;

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'estado_publicacion' => $producto->estado_publicacion,
                'precio_referencia' => (float)$producto->precio_referencia,
                'stock_actual' => $producto->stock_actual,
                'unidad_medida' => $producto->unidad_medida,
                'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
                'primera_imagen' => $rutaImagen ? asset('storage/' . $rutaImagen) : null,
            ];
        });
        return response()->json($productosFormateados);
    }

    // --- FUNCIÓN DE CARGAR PRODUCTO POR ID (EDITAR) ---
    public function getProductoPorId(Request $request, $id)
    {
        $user = $request->user();
        $producto = Producto::where('usuario_id', $user->id)
            ->where('id', $id)
            ->with(['imagenes', 'categoria:id,nombre'])
            ->firstOrFail();
        $data = $producto->toArray();
        $data['imagenes_actuales'] = $producto->imagenes->map(function ($imagen) {
            return [
                'id' => $imagen->id,
                'url' => asset('storage/' . $imagen->ruta_imagen),
                'principal' => $imagen->principal,
            ];
        });
        return response()->json($data);
    }

    // --- FUNCIÓN DE ACTUALIZAR PRODUCTO (EDITAR) ---
    public function updateProduct(Request $request, $id)
    {
        $user = $request->user();
        $producto = Producto::where('usuario_id', $user->id)->findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_referencia' => 'required|numeric|min:0.01',
            'unidad_medida' => 'nullable|string|max:50',
            'stock_actual' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'fecha_cosecha' => 'nullable|date',
            'imagenes_nuevas' => 'nullable|array',
            'imagenes_nuevas.*' => 'image|mimes:jpeg,png,jpg,webp|max:5000',
            'imagenes_existentes_ids' => 'nullable|array',
            'imagenes_existentes_ids.*' => 'exists:imagenes_producto,id',
        ]);

        try {
            DB::transaction(function () use ($request, $producto, $validated) {
                $producto->update([
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'],
                    'precio_referencia' => $validated['precio_referencia'],
                    'unidad_medida' => $validated['unidad_medida'],
                    'stock_actual' => $validated['stock_actual'],
                    'categoria_id' => $validated['categoria_id'],
                    'disponibilidad' => $validated['stock_actual'] > 0 ? 'disponible' : 'agotado',
                    'fecha_cosecha' => $validated['fecha_cosecha'] ?? null,
                ]);

                $idsAConservar = $validated['imagenes_existentes_ids'] ?? [];
                $imagenesABorrar = $producto->imagenes()->whereNotIn('id', $idsAConservar)->get();
                foreach ($imagenesABorrar as $imagen) {
                    $rutaStorage = 'public/' . $imagen->ruta_imagen;
                    if (Storage::exists($rutaStorage)) {
                        Storage::delete($rutaStorage);
                    }
                    $imagen->delete();
                }

                if ($request->hasFile('imagenes_nuevas')) {
                    foreach ($request->file('imagenes_nuevas') as $file) {
                        $path = $file->store('public/productos');
                        $rutaImagen = str_replace('public/', '', $path);
                        $producto->imagenes()->create(['ruta_imagen' => $rutaImagen, 'principal' => false]);
                    }
                }

                $producto->imagenes()->orderBy('id')->first()->update(['principal' => true]);
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar. ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Producto actualizado exitosamente.'], 200);
    }

    // --- FUNCIÓN DE ELIMINAR PRODUCTO ---
    public function destroyProduct(Request $request, $id)
    {
        $user = $request->user();
        try {
            $producto = Producto::findOrFail($id);
            if ($producto->usuario_id !== $user->id) {
                return response()->json(['message' => 'No autorizado para eliminar este producto.'], 403);
            }

            $imagenes = $producto->imagenes()->get();
            DB::transaction(function () use ($producto, $imagenes) {
                $producto->imagenes()->delete();
                $producto->delete();
                foreach ($imagenes as $imagen) {
                    $rutaStorage = 'public/' . $imagen->ruta_imagen;
                    if (Storage::exists($rutaStorage)) {
                        Storage::delete($rutaStorage);
                    }
                }
            });
            return response()->json(['message' => 'Producto eliminado exitosamente.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto: ID ' . $id . ' - ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al eliminar el producto.'], 500);
        }
    }


    protected function getProductosDataForExport($user)
    {
        $productos = Producto::where('usuario_id', $user->id)
            ->with('categoria:id,nombre')
            ->get();

        // Agregar BOM (Byte Order Mark) para asegurar que Excel reconozca UTF-8
        $csv = "\xEF\xBB\xBF";
        $csv .= "Nombre Producto,Categoría,Precio Ref (Q),Stock,Unidad,Estado\n";

        foreach ($productos as $producto) {
            $csv .= sprintf(
                "\"%s\",\"%s\",%.2f,%d,\"%s\",\"%s\"\n",
                str_replace('"', '""', $producto->nombre),
                $producto->categoria->nombre ?? 'N/A',
                $producto->precio_referencia,
                $producto->stock_actual,
                $producto->unidad_medida,
                str_replace('_', ' ', $producto->estado_publicacion)
            );
        }
        return $csv;
    }

    /**
     * Descarga CSV (Excel).
     */
    public function exportToExcel(Request $request)
    {
        $user = $request->user();
        $csvContent = $this->getProductosDataForExport($user);
        $filename = 'Mis_Productos_Agrolink_' . date('Ymd_His') . '.csv';

        // 💡 CAMBIO: Usamos response()->make para asegurar la descarga del contenido sin archivos temporales
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($csvContent, 200, $headers);
    }

    /**
     * Simula la generación y descarga de un reporte en PDF (HTML simple).
     */
    public function exportToPdf(Request $request)
    {
        $user = $request->user();
        $productos = Producto::where('usuario_id', $user->id)->with('categoria:id,nombre')->get();

        // 1. Generar contenido HTML válido
        $html = '<!DOCTYPE html><html lang=""><head><style>
                    body { font-family: sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .header { background-color: #4CAF50; color: white; padding: 15px; text-align: center; font-size: 20px;}
                </style></head><body>';
        $html .= '<div class="header">Reporte de Productos - Agrolink</div>';
        $html .= '<p>Generado por: ' . htmlspecialchars($user->primer_nombre . ' ' . $user->primer_apellido) . '</p>';
        $html .= '<p>Fecha de Generación: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<table><thead><tr><th>Producto</th><th>Categoría</th><th>Precio (Q)</th><th>Stock</th><th>Estado</th></tr></thead><tbody>';

        foreach ($productos as $producto) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($producto->nombre) . '</td>';
            $html .= '<td>' . htmlspecialchars($producto->categoria->nombre ?? 'N/A') . '</td>';
            $html .= '<td>Q' . number_format($producto->precio_referencia, 2) . '</td>';
            $html .= '<td>' . $producto->stock_actual . ' ' . htmlspecialchars($producto->unidad_medida) . '</td>';
            $html .= '<td>' . htmlspecialchars(str_replace('_', ' ', $producto->estado_publicacion)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        $filename = 'Productos_Agrolink_' . date('Ymd_His') . '.html'; // 💡 CAMBIADO A .html

        // Devolver una respuesta de descarga HTML
        return Response::stream(function () use ($html) {
            echo $html;
        }, 200, [
            'Content-Type' => 'text/html; charset=UTF-8', // 💡 CAMBIADO A TEXT/HTML
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    // --- FUNCIÓN QUE YA TENÍAS ---
    public function getPerfilCompleto(Request $request)
    {
        $user = $request->user();
        $perfil = PerfilProductor::where('usuario_id', $user->id)->first();
        $data = $user->toArray();
        // Cargar relaciones de geografía para obtener nombres
        $user->load(['pais', 'departamento', 'municipio', 'aldea']);
        $data['pais_nombre'] = $user->pais->nombre ?? null;
        $data['departamento_nombre'] = $user->departamento->nombre ?? null;
        $data['municipio_nombre'] = $user->municipio->nombre ?? null;
        $data['aldea_nombre'] = $user->aldea->nombre ?? null;

        if ($perfil) {
            $data = array_merge($data, $perfil->toArray());
        }
        unset($data['password']); // Quitar contraseña

        return response()->json($data);
    }

    // --- FUNCIÓN QUE YA TENÍAS ---
    public function storePerfilCompleto(Request $request)
    {
        $user = $request->user();
        $userFields = [ /* ... tus campos de user ... */];
        $productorFields = [ /* ... tus campos de productor ... */];
        // Re-añadir las validaciones completas aquí si es necesario
        $userFields = [
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'dpi' => 'nullable|string|max:20',
            'nit' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'correo' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'pais_id' => 'nullable|exists:paises,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'municipio_id' => 'nullable|exists:municipios,id',
            // 'aldea_id' => 'nullable|exists:aldeas,id', // Removido si no se usa
        ];
        $productorFields = [
            'whatsapp' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'ubicacion_lat' => 'nullable|numeric|between:-90,90',
            'ubicacion_lng' => 'nullable|numeric|between:-180,180',
        ];

        $validatedData = $request->validate(array_merge($userFields, $productorFields));

        try {
            DB::transaction(function () use ($user, $validatedData, $userFields, $productorFields) {
                $userData = array_intersect_key($validatedData, $userFields);
                $productorData = array_intersect_key($validatedData, $productorFields);
                $user->update($userData);
                PerfilProductor::updateOrCreate(
                    ['usuario_id' => $user->id],
                    $productorData
                );
            });
        } catch (\Exception $e) {
            Log::error('Error al guardar perfil completo: ' . $e->getMessage());
            return response()->json(['message' => 'Error: Falló al guardar los datos. ' . $e->getMessage()], 500);
        }
        return response()->json([
            'message' => 'Perfil completo guardado exitosamente.',
        ], 200);
    }


    /**
     * Obtiene el catálogo de productos (todos los productos aprobados).
     * Esta ruta será PÚBLICA, ya que cualquier consumidor debe poder verla.
     */
    // --- 💡 NUEVA FUNCIÓN: Catálogo de Productos (Consumidor) 💡 ---
    public function getProductsCatalog(Request $request)
    {
        try {
            // 1. Obtener todos los productos APROBADOS que tengan un usuario (productor) asociado
            $productos = Producto::where('estado_publicacion', 'aprobado')
                ->has('user') // 💡 CRÍTICO: Asegura que la relación 'user' NO es nula
                ->with([
                    'categoria:id,nombre',
                    'imagenes' => function ($query) {
                        $query->where('principal', true)->select('producto_id', 'ruta_imagen');
                    },
                    'user:id,primer_nombre,primer_apellido', // 💡 Cargar solo campos necesarios
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // 2. Formatear la respuesta
            $productosFormateados = $productos->map(function ($producto) {
                // Ya sabemos que $producto->user no es nulo gracias a has('user')
                $productorNombre = $producto->user->primer_nombre . ' ' . $producto->user->primer_apellido;

                $rutaImagen = ($producto->imagenes->first())
                    ? $producto->imagenes->first()->ruta_imagen
                    : null;

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio_referencia' => (float) $producto->precio_referencia,
                    'unidad_medida' => $producto->unidad_medida,
                    'categoria_nombre' => $producto->categoria->nombre ?? 'N/A',
                    'estado_publicacion' => $producto->estado_publicacion,
                    'fecha_cosecha' => $producto->fecha_cosecha,
                    'productor' => $productorNombre,
                    'productor_id' => $producto->user->id,
                    'imagen_url' => $rutaImagen ? asset('storage/' . $rutaImagen) : null,
                ];
            });

            return response()->json($productosFormateados);
        } catch (\Exception $e) {
            // Loguear el error exacto para depuración
            Log::error('FATAL ERROR al cargar catálogo de productos: ' . $e->getMessage() . ' en línea ' . $e->getLine());
            // Devolver un error amigable al frontend
            return response()->json(['message' => 'Error interno al cargar el catálogo. Por favor, contacte a soporte.'], 500);
        }
    }
    // En ProductorController
    public function getProductByIdPublic($id)
    {
        $producto = Producto::where('estado_publicacion', 'aprobado')
            ->with(['categoria:id,nombre', 'imagenes'])
            ->findOrFail($id);

        $imagen = $producto->imagenes->first();

        return response()->json([
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'precio_referencia' => (float) $producto->precio_referencia,
            'unidad_medida' => $producto->unidad_medida,
            'categoria_nombre' => $producto->categoria->nombre ?? 'N/A',
            'imagen_url' => $imagen?->url_publica,
        ]);
    }



}

