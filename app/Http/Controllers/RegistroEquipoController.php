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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;

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
        // Lógica de actualización de INSUMO
        if ($request->categoria === 'insumo') {
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
     * STORE (EQUIPO/INSUMO) - Genera QR dinámico
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

            // Crear el equipo primero sin el qr_code
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
                'qr_code'          => null, // Se actualizará después
                'estado'           => 'activo',
            ]);
            
            // Generar la URL completa del equipo
            $qrUrl = route('inventario.equipo', $equipo->id);
            
            // Actualizar el campo qr_code con la URL completa
            $equipo->update(['qr_code' => $qrUrl]);

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

            // ----------------------------------------------------
            // LÓGICA DE GENERACIÓN DINÁMICA DEL QR
            // ----------------------------------------------------
            // La URL ya fue generada y guardada en el campo qr_code
            
            // Generación real del QR en formato Base64
            try {
                // Genera el código QR como PNG y lo convierte a Base64
                $qrImage = QrCode::format('png')
                    ->size(250)
                    ->margin(2)
                    ->errorCorrection('H')
                    ->generate($qrUrl);
                
                // Convertir la imagen PNG a Base64
                $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
                
            } catch (\Exception $e) {
                // Fallback si la librería no está disponible o falla
                Log::error("QR Generation Failed: " . $e->getMessage());
                
                // Opción 1: Usar una imagen placeholder
                if (file_exists(public_path('images/qr-placeholder.png'))) {
                    $qrBase64 = "data:image/png;base64," . base64_encode(file_get_contents(public_path('images/qr-placeholder.png')));
                } else {
                    // Opción 2: Crear un QR simple con una API externa como fallback
                    $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl);
                }
            }

            return redirect()->route('registro_equipo.create')
                ->with('success', 'Equipo registrado correctamente.')
                ->with('qr_generado', true)
                ->with('qr_base64', $qrBase64)
                ->with('equipo_id', $equipo->id);
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
            return redirect()->route('registro_equipo.create')->with('success', 'Insumo registrado correctamente.');
        }
    }

    /**
     * Buscar datos del equipo utilizando IA (Google Gemini)
     */
    public function buscarConIA(Request $request)
    {
        $serialNumber = $request->query('numero_serie'); 
        $apiKey = env('GEMINI_API_KEY');
        
        if (!$serialNumber) {
            return response()->json(['error' => 'Número de serie es requerido.'], 400);
        }
        if (!$apiKey) {
            Log::error('API Key Missing: GEMINI_API_KEY is not configured.');
            return response()->json(['error' => 'La clave de API (GEMINI_API_KEY) no está configurada.'], 500);
        }

        // 1. Definir Endpoint y Modelo de Gemini
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        
        // 2. Definir el prompt y la estructura JSON
        $prompt = "Actúa como un experto en inventario de TI. Busca en la web el modelo de equipo asociado al número de serie o fragmento de serie: '{$serialNumber}'. Es muy común que un fragmento de serie esté asociado a varios modelos. Si encuentras más de uno, devuelve una lista de posibles modelos. Responde ÚNICAMENTE con un objeto JSON que contenga un array de posibles modelos bajo la clave 'opciones_modelos'. Cada elemento del array debe ser un objeto con las siguientes claves: 'marca', 'modelo', 'tipo_equipo_nombre' (ej: Notebook), 'precio' (valor numérico estimado) y 'especificaciones_clave' (string de resumen). Si solo encuentras un resultado claro, devuelve un array de un solo elemento. Si no encuentras nada, devuelve un array vacío. Asegúrate que el precio sea numérico.";
        
        // 3. Esquema JSON para la respuesta (pedimos un array de objetos)
        $responseSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'opciones_modelos' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'marca' => ['type' => 'STRING'],
                            'modelo' => ['type' => 'STRING'],
                            'tipo_equipo_nombre' => ['type' => 'STRING'],
                            'precio' => ['type' => 'NUMBER'],
                            'especificaciones_clave' => ['type' => 'STRING'],
                        ]
                    ]
                ]
            ],
            'required' => ['opciones_modelos']
        ];
        
        // 4. Preparar la solicitud a la API de Gemini
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post("{$endpoint}?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $responseSchema,
                    ]
                ]);

            // 5. Manejar la respuesta
            if ($response->successful()) {
                $content = $response->json();
                
                // Extracción segura del texto JSON de la respuesta de Gemini
                $iaResponseText = trim($content['candidates'][0]['content']['parts'][0]['text'] ?? '');
                $data = json_decode($iaResponseText, true);

                // 6. Procesar las opciones
                if (json_last_error() === JSON_ERROR_NONE && isset($data['opciones_modelos'])) {
                    $opciones = $data['opciones_modelos'];
                    
                    if (empty($opciones)) {
                        return response()->json(['success' => true, 'opciones' => [], 'error' => 'No se encontraron modelos coincidentes.'], 200);
                    }

                    // Procesar las opciones para incluir el ID local de TipoEquipo
                    $opcionesProcesadas = collect($opciones)->map(function ($opcion) {
                        $tipoNombre = $opcion['tipo_equipo_nombre'] ?? null;
                        $tipoEquipoId = null;

                        if ($tipoNombre) {
                            $tipoEquipo = TipoEquipo::where('nombre', 'LIKE', "%{$tipoNombre}%")->first();
                            $tipoEquipoId = $tipoEquipo->id ?? null;
                        }

                        $opcion['tipo_equipo_id'] = $tipoEquipoId;
                        $opcion['tipo_equipo_sugerido'] = $tipoNombre;
                        $opcion['precio'] = is_numeric($opcion['precio'] ?? '') ? (float)($opcion['precio']) : 0;
                        return $opcion;
                    })->all();

                    return response()->json(['success' => true, 'opciones' => $opcionesProcesadas], 200);

                } else {
                    Log::error('Gemini response JSON parse failed', ['response' => $iaResponseText, 'error_message' => json_last_error_msg()]);
                    return response()->json(['success' => false, 'error' => 'Respuesta de IA mal formateada (JSON).'], 422);
                }

            } else {
                Log::error('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Error al contactar a la API de Gemini.'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Gemini Request Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Error de conexión o timeout: ' . $e->getMessage()], 500);
        }
    }
}
