<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\EspecificacionTecnica;
use App\Models\TipoEquipo;
use App\Models\Proveedor;
use App\Models\EstadoEquipo;
use App\Models\Documento;
use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Asignacion;
use App\Models\AsignacionInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistroEquipoController extends Controller
{
    // Autocompletar nombres de insumos
    public function autocompleteInsumos(Request $request)
    {
        $termino = $request->get('term');

        $resultados = Insumo::where('nombre', 'LIKE', '%' . $termino . '%')
            ->select('nombre')
            ->distinct()
            ->take(10)
            ->get()
            ->pluck('nombre');

        return response()->json($resultados);
    }

    // Formulario de creación
    public function create()
    {
        $tipos             = TipoEquipo::all();
        $proveedores       = Proveedor::all();
        $estados           = EstadoEquipo::all();
        $facturas          = Documento::where('tipo', 'factura')->get();
        $garantias         = Documento::where('tipo', 'garantia')->get();
        $insumosExistentes = Insumo::select('nombre')->distinct()->get();
        $sucursales        = Sucursal::all();
        $usuarios          = Usuario::all();

        return view('registro_equipo', compact(
            'tipos',
            'proveedores',
            'estados',
            'facturas',
            'garantias',
            'insumosExistentes',
            'sucursales',
            'usuarios'
        ));
    }

    // Eliminar equipo/insumo
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'password_confirm' => 'required|string',
        ]);

        if (!Hash::check($request->password_confirm, auth()->user()->password)) {
            return redirect()->route('inventario')
                ->withErrors(['password_confirm' => 'La contraseña ingresada no es correcta']);
        }

        $model = Equipo::find($id) ?? Insumo::find($id);

        if (!$model) {
             return redirect()->route('inventario')->with('error', 'Item no encontrado.');
        }

        $model->delete();

        return redirect()->route('inventario')->with('success', 'Item eliminado correctamente.');
    }

    // Actualizar insumo o equipo
    public function update(Request $request, $id)
    {
        if ($request->categoria === 'insumo') {
            // Lógica de actualización de INSUMO
            $insumo      = Insumo::findOrFail($id);
            $oldSucursal = $insumo->sucursal_id;

            $request->validate([
                'nombre'            => 'nullable|string|max:255',
                'cantidad'          => 'nullable|integer|min:0',
                'estado_equipo_id'  => 'nullable|exists:estados_equipo,id',
                'proveedor_id'      => 'nullable|exists:proveedores,id',
                'sucursal_id'       => 'nullable|exists:sucursales,id',
                'precio'            => 'nullable|numeric|min:0',
                'usuario_id'        => 'nullable|exists:usuarios,id',
                'cantidad_asignada' => 'nullable|integer|min:1',
                'motivo'            => 'nullable|string|max:255',
            ]);

            $insumo->update($request->only([
                'nombre', 'cantidad', 'estado_equipo_id', 'proveedor_id', 'sucursal_id', 'precio',
            ]));

            if ($request->filled('sucursal_id') && $oldSucursal != $request->sucursal_id) {
                $sucursalNombre = Sucursal::find($request->sucursal_id)?->nombre ?? 'N/A';
                $insumo->registrarMovimiento('Cambio de Sucursal', 'Sucursal cambiada a: ' . $sucursalNombre);
            }
            if ($request->filled('usuario_id')) {
                AsignacionInsumo::where('insumo_id', $insumo->id)->whereNull('fecha_fin')->update(['fecha_fin' => now()]);
                AsignacionInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'usuario_id'       => $request->usuario_id,
                    'cantidad'         => $request->cantidad_asignada ?? 1,
                    'fecha_asignacion' => now(),
                    'motivo'           => $request->motivo ?? 'Cambio de Usuario',
                ]);
            } else {
                AsignacionInsumo::where('insumo_id', $insumo->id)->whereNull('fecha_fin')->update(['fecha_fin' => now(), 'motivo'    => $request->motivo ?? 'Desasignación de usuario']);
            }

            return redirect()->route('inventario.insumo', $insumo->id)->with('success', 'Insumo actualizado correctamente.');
        }

        // Lógica de actualización de EQUIPO
        $equipo      = Equipo::findOrFail($id);
        $oldSucursal = $equipo->sucursal_id;

        $request->validate([
            'marca'            => 'nullable|string|max:100',
            'modelo'           => 'nullable|string|max:100',
            'numero_serie'     => 'nullable|string|max:100|unique:equipos,numero_serie,' . $equipo->id,
            'estado_equipo_id' => 'nullable|exists:estados_equipo,id',
            'proveedor_id'     => 'nullable|exists:proveedores,id',
            'sucursal_id'      => 'nullable|exists:sucursales,id',
            'precio'           => 'nullable|numeric|min:0',
            'usuario_id'       => 'nullable|exists:usuarios,id',
            'motivo'           => 'nullable|string|max:255',
        ]);

        $equipo->update($request->only([
            'marca', 'modelo', 'numero_serie', 'estado_equipo_id', 'proveedor_id', 'sucursal_id', 'precio',
        ]));

        if ($request->filled('usuario_id')) {
            Asignacion::where('equipo_id', $equipo->id)->whereNull('fecha_fin')->update(['fecha_fin' => now()]);
            Asignacion::create([
                'equipo_id'        => $equipo->id,
                'usuario_id'       => $request->usuario_id,
                'fecha_asignacion' => now(),
                'motivo'           => $request->motivo ?? 'Cambio de Usuario',
            ]);
        } else {
            Asignacion::where('equipo_id', $equipo->id)->whereNull('fecha_fin')->update(['fecha_fin' => now(), 'motivo'    => $request->motivo ?? 'Desasignación de usuario']);
        }

        return redirect()->route('inventario.equipo', $equipo->id)->with('success', 'Equipo actualizado correctamente.');
    }

    /**
     * ============================
     * STORE (EQUIPO/INSUMO)
     * ============================
     */
    public function store(Request $request)
    {
        if ($request->categoria === 'equipo') {
            $request->validate([
                'tipo_equipo_id'   => 'required|exists:tipos_equipo,id',
                'proveedor_id'     => 'nullable|exists:proveedores,id',
                'estado_equipo_id' => 'nullable|exists:estados_equipo,id',
                'sucursal_id'      => 'nullable|exists:sucursales,id',
                'marca'            => 'nullable|string|max:100',
                'modelo'           => 'nullable|string|max:100',
                'numero_serie'     => 'nullable|string|max:100|unique:equipos,numero_serie',
                'precio'           => 'nullable|numeric|min:0',
                'fecha_compra'     => 'nullable|date',
                'fecha_registro'   => 'nullable|date',
                'usuario_id'       => 'nullable|exists:usuarios,id',
                'motivo'           => 'nullable|string|max:255',
                'especificaciones_ia' => 'nullable|string', 
            ]);

            $estadoDisponibleId = EstadoEquipo::where('nombre', 'Disponible')->first()->id;

            $equipo = Equipo::create([
                'tipo_equipo_id'   => $request->tipo_equipo_id,
                'proveedor_id'     => $request->proveedor_id,
                'estado_equipo_id' => $request->estado_equipo_id ?? $estadoDisponibleId,
                'sucursal_id'      => $request->sucursal_id,
                'marca'            => $request->marca,
                'modelo'           => $request->modelo,
                'numero_serie'     => $request->numero_serie,
                'precio'           => $request->precio,
                'fecha_compra'     => $request->fecha_compra,
                'fecha_registro'   => $request->fecha_registro ?? now(),
                'qr_code'          => $request->numero_serie ?? ('EQ-' . now()->format('YmdHis')),
                'estado'           => 'activo',
            ]);

            $equipo->registrarMovimiento('Registro de Equipo', 'Equipo creado en el sistema');

            if ($request->filled('usuario_id')) {
                Asignacion::create([
                    'equipo_id'        => $equipo->id,
                    'usuario_id'       => $request->usuario_id,
                    'fecha_asignacion' => now(),
                    'motivo'           => $request->motivo ?? 'Asignación inicial',
                ]);
            }

            // Guardar la especificación (incluye resumen_ia en la columna resumen_ia)
            $especificacionData = $request->only((new EspecificacionTecnica)->getFillable());
            $especificacionData['resumen_ia'] = $request->especificaciones_ia ?? null; 
            
            EspecificacionTecnica::create(
                array_merge(
                    ['equipo_id' => $equipo->id],
                    $especificacionData
                )
            );

            return redirect()->route('registro_equipo.create')->with('success', 'Equipo registrado.')->with('qr_generado', true);
        }
        
        // Lógica de STORE INSUMO
        if ($request->categoria === 'insumo') {
             $request->validate([
                'nombre_insumo'     => 'required|string|max:255',
                'cantidad'          => 'required|integer|min:0',
                'estado_equipo_id'  => 'nullable|exists:estados_equipo,id',
                'proveedor_id'      => 'nullable|exists:proveedores,id',
                'sucursal_id'       => 'nullable|exists:sucursales,id',
                'precio'            => 'nullable|numeric|min:0',
                'fecha_compra'      => 'nullable|date',
                'fecha_registro'    => 'nullable|date',
                'usuario_id'        => 'nullable|exists:usuarios,id',
                'cantidad_asignada' => 'nullable|integer|min:1',
                'motivo'            => 'nullable|string|max:255',
            ]);

            $estadoDisponibleId = EstadoEquipo::where('nombre', 'Disponible')->first()->id;

            $insumo = Insumo::create([
                'nombre'           => $request->nombre_insumo,
                'cantidad'         => $request->cantidad,
                'estado_equipo_id' => $request->estado_equipo_id ?? $estadoDisponibleId,
                'proveedor_id'     => $request->proveedor_id,
                'sucursal_id'      => $request->sucursal_id,
                'precio'           => $request->precio,
                'fecha_compra'     => $request->fecha_compra,
                'fecha_registro'   => $request->fecha_registro ?? now(),
            ]);

            $insumo->registrarMovimiento('Registro de Insumo', 'Insumo creado en el sistema');

            if ($request->filled('usuario_id')) {
                AsignacionInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'usuario_id'       => $request->usuario_id,
                    'cantidad'         => $request->cantidad_asignada ?? 1,
                    'fecha_asignacion' => now(),
                    'motivo'           => $request->motivo ?? 'Asignación inicial',
                ]);
            }
            return redirect()->route('registro_equipo.create')->with('success', 'Insumo registrado.');
        }
    }

    /**
     * Buscar datos del equipo utilizando IA (Google Gemini)
     * Parámetros esperados: numero_serie
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarConIA(Request $request)
    {
        // Usamos query() ya que la ruta es GET
        $serialNumber = $request->query('numero_serie'); 

        // Depuración: Si el serial está vacío, el front-end debería haberlo capturado,
        // pero lo chequeamos en el back-end.
        if (!$serialNumber) {
            Log::error('Validation Failed: Serial number is missing in the request.');
            return response()->json(['error' => 'Número de serie es requerido.'], 400);
        }

        // 1. Obtener la API Key de GEMINI
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('API Key Missing: GEMINI_API_KEY is not configured in .env');
            return response()->json(['error' => 'La clave de API (GEMINI_API_KEY) no está configurada.'], 500);
        }

        // 2. Definir Endpoint y Modelo de Gemini
        // Usamos la URL base estándar de generateContent de Gemini.
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        
        // 3. Definir el prompt y la estructura JSON
        $prompt = "Actúa como un experto en inventario de TI. Busca en la web el modelo de equipo asociado al número de serie o fragmento de serie: '{$serialNumber}'. Responde ÚNICAMENTE con un objeto JSON que contenga las propiedades 'marca', 'modelo', 'precio' (valor numérico estimado) y 'especificaciones_clave' (un string conciso con RAM, CPU y almacenamiento). Si no encuentras información específica o el precio, usa el valor 'Desconocido' o 0 para el precio. Además, incluye la propiedad 'tipo_equipo_nombre' (ej: Notebook, Monitor, Impresora) para que podamos clasificarlo. Asegúrate de que el precio sea un número entero o flotante, no una cadena.";
        
        // 4. Preparar la solicitud a la API de Gemini
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            // La clave va en el query param para Gemini, no en el Authorization header
            ->post("{$endpoint}?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [ // CORRECCIÓN: Se cambió 'config' a 'generationConfig'
                    'responseMimeType' => 'application/json', 
                ]
            ]);

            // 5. Manejar la respuesta
            if ($response->successful()) {
                $content = $response->json();
                
                // Extracción segura del texto JSON de la respuesta de Gemini
                $iaResponseText = trim($content['candidates'][0]['content']['parts'][0]['text'] ?? '');
                $data = json_decode($iaResponseText, true);

                // 6. Devolver los datos al frontend si el JSON es válido
                if (json_last_error() === JSON_ERROR_NONE && isset($data['marca'])) {
                    $tipoNombre = $data['tipo_equipo_nombre'] ?? null;
                    $tipoEquipoId = null;

                    if ($tipoNombre) {
                        // Búsqueda flexible usando LIKE
                        $tipoEquipo = TipoEquipo::where('nombre', 'LIKE', "%{$tipoNombre}%")->first();
                        $tipoEquipoId = $tipoEquipo->id ?? null;
                    }

                    return response()->json([
                        'success' => true,
                        'marca' => $data['marca'] ?? '',
                        'modelo' => $data['modelo'] ?? '',
                        'precio' => is_numeric($data['precio'] ?? '') ? (float)($data['precio']) : 0,
                        'especificaciones_clave' => $data['especificaciones_clave'] ?? '',
                        'tipo_equipo_id' => $tipoEquipoId,
                        'tipo_equipo_sugerido' => $tipoNombre,
                    ]);
                }
                
                Log::error('Gemini response JSON parse failed', ['response' => $iaResponseText]);
                return response()->json([
                    'success' => false, 'error' => 'Respuesta de IA mal formateada o incompleta.', 'raw_response' => $iaResponseText
                ], 422);

            } else {
                // Manejar error de la API (ej. 401, 429)
                Log::error('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Error al contactar a la API de Gemini.'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Gemini Request Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Error de conexión o timeout: ' . $e->getMessage()], 500);
        }
    }
    
    // Función edit (Omitida por brevedad)
    public function edit($id) { /* ... */ }
    
    // Función storeInsumo (Omitida por brevedad)
    public function storeInsumo(Request $request) { /* ... */ return $this->store($request); }
}
