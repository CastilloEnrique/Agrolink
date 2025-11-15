<?php

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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Response; // 💡 Nuevo Import para PDF/Descarga limpia
use Dompdf\Dompdf;
use Dompdf\Options;

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

                        $rutaImagen = $file->store('productos', 'public');



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
                'primera_imagen' => $rutaImagen ? asset('storage/' . ltrim($rutaImagen, '/'))
                    : null,
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
                        // ✅ CORRECTO
                        $rutaImagen = $file->store('productos', 'public');
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



    /**
     * Descarga CSV (Excel).
     */
    public function exportToExcel(Request $request)
    {
        $user = $request->user();
        $productos = Producto::where('usuario_id', $user->id)
            ->with('categoria:id,nombre', 'imagenes')
            ->orderBy('created_at', 'desc')
            ->get();

        // Crear libro y hoja
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mis Productos');

        // Encabezados (coinciden con la DataTable)
        $headers = ['Producto', 'Categoría', 'Estado', 'Precio (Q)', 'Stock', 'Unidad'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Contenido
        $row = 2;
        foreach ($productos as $producto) {
            $sheet->setCellValue('A' . $row, $producto->nombre);
            $sheet->setCellValue('B' . $row, $producto->categoria->nombre ?? 'N/A');
            $sheet->setCellValue('C' . $row, ucfirst(str_replace('_', ' ', $producto->estado_publicacion)));
            $sheet->setCellValue('D' . $row, $producto->precio_referencia);
            $sheet->setCellValue('E' . $row, $producto->stock_actual);
            $sheet->setCellValue('F' . $row, $producto->unidad_medida);
            $row++;
        }

        // Descargar archivo
        $filename = 'Mis_Productos_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return response($excelOutput, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Simula la generación y descarga de un reporte en PDF (HTML simple).
     */

    public function exportToPdf(Request $request)
    {
        $user = $request->user();
        $productos = Producto::where('usuario_id', $user->id)
            ->with('categoria:id,nombre')
            ->orderBy('created_at', 'desc')
            ->get();

        // HTML simple basado en las columnas visibles
        $html = '
    <h2 style="text-align:center;">Reporte de Productos - Agrolink</h2>
    <p><strong>Usuario:</strong> ' . htmlspecialchars($user->primer_nombre . ' ' . $user->primer_apellido) . '</p>
    <p><strong>Fecha:</strong> ' . date('Y-m-d H:i') . '</p>
    <table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr style="background:#f2f2f2;">
                <th>Producto</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Precio (Q)</th>
                <th>Stock</th>
                <th>Unidad</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($productos as $producto) {
            $html .= '
        <tr>
            <td>' . htmlspecialchars($producto->nombre) . '</td>
            <td>' . htmlspecialchars($producto->categoria->nombre ?? 'N/A') . '</td>
            <td>' . ucfirst(str_replace('_', ' ', $producto->estado_publicacion)) . '</td>
            <td>Q' . number_format($producto->precio_referencia, 2) . '</td>
            <td>' . $producto->stock_actual . '</td>
            <td>' . htmlspecialchars($producto->unidad_medida) . '</td>
        </tr>';
        }

        $html .= '</tbody></table>';

        // Configurar Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Descargar
        $filename = 'Mis_Productos_' . date('Ymd_His') . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
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
                Rule::unique('usuarios', 'correo')->ignore($user->id),
            ],
            'pais_id' => 'nullable|exists:pais,id',
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
                    'imagen_url' => $rutaImagen ? asset('storage/' . ltrim($rutaImagen, '/'))
                        : null,
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
            ->with([
                'categoria:id,nombre',
                'imagenes', // <- Bien: 'imagenes' ya está en el 'with'
                'user:id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,pais_id,departamento_id,municipio_id,aldea_id,telefono,correo',
                'user.pais:id,nombre',
                'user.departamento:id,nombre',
                'user.municipio:id,nombre',
                'user.aldea:id,nombre',
                'user.perfilProductor'
            ])
            ->findOrFail($id);

        // --- 🔴 CAMBIO 1: YA NO BUSCAMOS SOLO LA PRIMERA IMAGEN ---
        // $imagen = $producto->imagenes->first(); // <-- Esta línea se elimina

        // --- ✅ CAMBIO 2: MAPEAMOS TODAS LAS IMÁGENES A SUS URLS ---
        $imagenesUrls = $producto->imagenes->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => asset('storage/' . ltrim($img->ruta_imagen, '/')),
                'principal' => $img->principal
            ];
        });

        // ------------------------------
        // 🔵 NOMBRE COMPLETO DEL PRODUCTOR (Sin cambios)
        // ------------------------------
        $productorNombre = trim(
            $producto->user->primer_nombre . ' ' .
            $producto->user->segundo_nombre . ' ' .
            $producto->user->primer_apellido . ' ' .
            $producto->user->segundo_apellido
        );

        // -------------------------------------
        // 🟣 UBICACIÓN (Sin cambios)
        // -------------------------------------
        $ubicacion =
            $producto->user->aldea->nombre ??
            $producto->user->municipio->nombre ??
            $producto->user->departamento->nombre ??
            'Ubicación no disponible';

        // --- ✅ CAMBIO 3: AJUSTAMOS EL JSON DE RESPUESTA ---
        return response()->json([
            // Datos del producto
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'precio_referencia' => (float) $producto->precio_referencia,
            'unidad_medida' => $producto->unidad_medida,
            'categoria_nombre' => $producto->categoria->nombre ?? 'N/A',

            // --- 💡 AQUÍ ESTÁ LA MODIFICACIÓN ---
            // 'imagen_url' => $imagen ? asset('storage/' . ltrim($imagen->ruta_imagen, '/')) : null, // <-- Esta línea se reemplaza
            'imagenes' => $imagenesUrls, // <-- por esta línea (es un array)

            // Datos del productor
            'productor' => $productorNombre,
            'productor_id' => $producto->user->id,
            'whatsapp' => $producto->user->perfilProductor->whatsapp ?? $producto->user->telefono,

            // Ubicación
            'ubicacion_nombre' => $ubicacion,

            // Datos adicionales
            'fecha_cosecha' => $producto->fecha_cosecha,
            'stock_actual' => $producto->stock_actual,
        ]);
    }
//
    public function contactarWhatsapp($id)
    {
        $producto = Producto::with(['user.perfilProductor'])->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $productor = $producto->user;

        // 1️⃣ Número desde perfil_productor (whatsapp)
        $numero = $productor->perfilProductor->whatsapp ?? null;

        // 2️⃣ Si no hay en perfil_productor, usar el teléfono del usuario
        if (!$numero) {
            $numero = $productor->telefono ?? null;
        }

        // 3️⃣ Si no hay ninguno → error
        if (!$numero) {
            return response()->json(['message' => 'El productor no tiene un número configurado'], 400);
        }

        // Limpieza de caracteres no numéricos (opcional)
        $numero = preg_replace('/[^0-9]/', '', $numero);

        // Mensaje de WhatsApp
        $mensaje = urlencode(
            "Hola {$productor->primer_nombre}, vi tu producto \"{$producto->nombre}\" en Agrolink y estoy interesado. ¿Está disponible?"
        );

        // Link oficial de WhatsApp
        $url = "https://wa.me/{$numero}?text={$mensaje}";

        return response()->json([
            'whatsapp_url' => $url,
            'numero' => $numero,
            'productor' => $productor->primer_nombre,
            'producto' => $producto->nombre
        ]);
    }

    public function productosRelacionados($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $relacionados = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $id)
            ->whereIn('estado_publicacion', ['aprobado'])

            ->with([
                'imagenes' => function ($q) {
                    $q->where('principal', true)->select('producto_id', 'ruta_imagen');
                },
                'categoria:id,nombre',
            ])
            ->limit(6)
            ->get();

        // FORMATEO DE RESPUESTA
        $data = $relacionados->map(function ($p) {

            $img = $p->imagenes->first();
            $rutaImagen = $img ? asset('storage/' . ltrim($img->ruta_imagen, '/')) : null;


            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'precio_referencia' => (float) $p->precio_referencia,
                'categoria_nombre' => $p->categoria->nombre ?? 'N/A',
                'imagen_url' => $rutaImagen,
            ];
        });

        return response()->json($data);
    }


    public function perfilPublico($id)
    {
        $usuario = Usuario::with([
            'perfilProductor.aldea.municipio.departamento',
        ])->find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Productor no encontrado'], 404);
        }

        return response()->json([
            'nombre' => $usuario->primer_nombre . ' ' . $usuario->primer_apellido,
            'foto' => $usuario->foto_perfil_url,
            'telefono' => $usuario->telefono,
            'aldea' => $usuario->aldea->nombre ?? null,
            'municipio' => $usuario->municipio->nombre ?? null,
            'departamento' => $usuario->departamento->nombre ?? null,
            'productos' => $usuario->perfilProductor->productos ?? [],
        ]);
    }



}

