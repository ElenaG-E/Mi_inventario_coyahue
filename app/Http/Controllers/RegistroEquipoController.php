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
use Illuminate\Support\Facades\Storage; // Importación necesaria

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
        // Mostrar TODOS los documentos (disponibles o ya asociados)
        $facturas          = Documento::where('tipo', 'factura')->get();
        $garantias         = Documento::where('tipo', 'garantia')->get();

        $insumosExistentes = Insumo::select('nombre')->distinct()->get();
        $sucursales        = Sucursal::all();
        $usuarios          = Usuario::all();

        return view('registro_equipo', compact(
            'tipos',
            'proveedores',
            'estados',
            'facturas', // Ahora contiene todos los documentos de tipo factura
            'garantias', // Ahora contiene todos los documentos de tipo garantía
            'insumosExistentes',
            'sucursales',
            'usuarios'
        ));
    }

    // Eliminar equipo o insumo
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

    // Actualizar equipo o insumo
    public function update(Request $request, $id)
    {
        if ($request->categoria === 'insumo') {
            return $this->updateInsumo($request, $id);
        } else {
            return $this->updateEquipo($request, $id);
        }
    }

    private function updateInsumo(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
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
            'nombre',
            'cantidad',
            'estado_equipo_id',
            'proveedor_id',
            'sucursal_id',
            'precio',
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
            AsignacionInsumo::where('insumo_id', $insumo->id)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now(),
                    'motivo'    => $request->motivo ?? 'Desasignación de usuario'
                ]);
        }

        return redirect()->route('inventario.insumo', $insumo->id)
                         ->with('success', 'Insumo actualizado correctamente.');
    }

    private function updateEquipo(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
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
            'marca',
            'modelo',
            'numero_serie',
            'estado_equipo_id',
            'proveedor_id',
            'sucursal_id',
            'precio',
        ]));

        if ($request->filled('sucursal_id') && $oldSucursal != $request->sucursal_id) {
            $sucursalNombre = Sucursal::find($request->sucursal_id)?->nombre ?? 'N/A';
            $equipo->registrarMovimiento('Cambio de Sucursal', 'Sucursal cambiada a: ' . $sucursalNombre);
        }

        if ($request->estado_equipo_id == 4) {
            $equipo->registrarMovimiento('Baja', 'Equipo dado de baja');
        }

        if ($request->filled('usuario_id')) {
            Asignacion::where('equipo_id', $equipo->id)->whereNull('fecha_fin')->update(['fecha_fin' => now()]);

            Asignacion::create([
                'equipo_id'        => $equipo->id,
                'usuario_id'       => $request->usuario_id,
                'fecha_asignacion' => now(),
                'motivo'           => $request->motivo ?? 'Cambio de Usuario',
            ]);
        } else {
            Asignacion::where('equipo_id', $equipo->id)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now(),
                    'motivo'    => $request->motivo ?? 'Desasignación de usuario'
                ]);
        }

        return redirect()->route('inventario.equipo', $equipo->id)
                         ->with('success', 'Equipo actualizado correctamente.');
    }

    // Store para equipos e insumos (redirige)
    public function store(Request $request)
    {
        if ($request->categoria === 'equipo') {
            return $this->storeEquipo($request);
        } elseif ($request->categoria === 'insumo') {
            return $this->storeInsumoData($request);
        }
        return redirect()->route('registro_equipo.create')->with('error', 'Debe seleccionar una categoría válida.');
    }

    // Store para insumos (ya no es necesario, pero lo mantengo si tienes rutas separadas)
    public function storeInsumo(Request $request)
    {
        return $this->storeInsumoData($request);
    }

    private function storeEquipo(Request $request)
    {
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
            // Validaciones de documentos
            'documentos_factura.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documentos_garantia.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $estadoDisponibleId = EstadoEquipo::where('nombre','Disponible')->first()->id ?? 1;

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
            'qr_code'          => null,
            'estado'           => 'activo',
        ]);

        $qrUrl = route('inventario.equipo', $equipo->id);
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

        $especificacionesData = $request->only((new EspecificacionTecnica)->getFillable());
        $especificacionesData['resumen_ia'] = $request->especificaciones_ia ?? null;
        
        if (!empty(array_filter($especificacionesData))) {
            EspecificacionTecnica::create(array_merge(
                ['equipo_id' => $equipo->id],
                $especificacionesData
            ));
        }

        // ✅ CORRECCIÓN: Pasar la Clase completa para el tipo polimórfico
        $this->procesarDocumentos($request, $equipo, Equipo::class); 
        
        // Lógica de generación de QR para el pop-up
        try {
            $qrImage = QrCode::format('png')
                ->size(250)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($qrUrl);
            
            $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        } catch (\Exception $e) {
            Log::error("QR Generation Failed: " . $e->getMessage());
            $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl);
        }

        return redirect()->route('registro_equipo.create')
                         ->with('success', 'Equipo registrado correctamente.')
                         ->with('qr_generado', true)
                         ->with('qr_base64', $qrBase64)
                         ->with('equipo_id', $equipo->id);
    }

    private function storeInsumoData(Request $request)
    {
        $request->validate([
            'nombre_insumo'    => 'required|string|max:255',
            'cantidad'         => 'required|integer|min:1',
            'estado_equipo_id' => 'nullable|exists:estados_equipo,id',
            'proveedor_id'     => 'nullable|exists:proveedores,id',
            'sucursal_id'      => 'nullable|exists:sucursales,id',
            'precio'           => 'nullable|numeric|min:0',
            'fecha_compra'     => 'nullable|date',
            'fecha_registro'   => 'nullable|date',
            'usuario_id'       => 'nullable|exists:usuarios,id',
            'cantidad_asignada'=> 'nullable|integer|min:1',
            'motivo'           => 'nullable|string|max:255',
            // Validaciones de documentos
            'documentos_factura.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documentos_garantia.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $estadoDisponibleId = EstadoEquipo::where('nombre','Disponible')->first()->id ?? 1;

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

        // ✅ CORRECCIÓN: Pasar la Clase completa para el tipo polimórfico
        $this->procesarDocumentos($request, $insumo, Insumo::class);

        return redirect()->route('registro_equipo.create')
                         ->with('success', 'Insumo registrado correctamente.');
    }

    /**
     * Procesa la subida de documentos (facturas y garantías) y los asocia a un modelo.
     *
     * @param Request $request
     * @param Model $model El modelo a asociar (Equipo o Insumo)
     * @param string $documentableType El tipo de modelo polimórfico (e.g., Equipo::class)
     */
    protected function procesarDocumentos(Request $request, $model, string $documentableType)
    {
        // Procesar facturas subidas
        if ($request->hasFile('documentos_factura')) {
            foreach ($request->file('documentos_factura') as $archivo) {
                if ($archivo->isValid()) {
                    $nombreArchivo = $archivo->getClientOriginalName();
                    
                    // ✅ CORRECCIÓN 1: Usamos el disco 'public'
                    $ruta = $archivo->store('documentos', 'public'); 
                    
                    Documento::create([
                        'nombre_archivo'    => $nombreArchivo,
                        'ruta_s3'           => $ruta, // ✅ CORRECCIÓN 2: Corregida la columna a 'ruta_s3'
                        'clave_s3'          => basename($ruta),
                        'tipo'              => 'factura',
                        'mime_type'         => $archivo->getMimeType(), // Añadidos campos
                        'tamaño_bytes'      => $archivo->getSize(),
                        'fecha_subida'      => now(), // Añadidos campos
                        'documentable_id'   => $model->id,
                        'documentable_type' => $documentableType,
                        'usuario_id'        => auth()->id(),
                    ]);
                }
            }
        }

        // Procesar garantías subidas
        if ($request->hasFile('documentos_garantia')) {
            foreach ($request->file('documentos_garantia') as $archivo) {
                if ($archivo->isValid()) {
                    $nombreArchivo = $archivo->getClientOriginalName();
                    
                    // ✅ CORRECCIÓN 1: Usamos el disco 'public'
                    $ruta = $archivo->store('documentos', 'public'); 

                    Documento::create([
                        'nombre_archivo'    => $nombreArchivo,
                        'ruta_s3'           => $ruta, // ✅ CORRECCIÓN 2: Corregida la columna a 'ruta_s3'
                        'clave_s3'          => basename($ruta),
                        'tipo'              => 'garantia',
                        'mime_type'         => $archivo->getMimeType(), // Añadidos campos
                        'tamaño_bytes'      => $archivo->getSize(),
                        'fecha_subida'      => now(), // Añadidos campos
                        'documentable_id'   => $model->id,
                        'documentable_type' => $documentableType,
                        'usuario_id'        => auth()->id(),
                        'tiempo_garantia_meses' => $request->input('tiempo_garantia_meses') ?? null, 
                    ]);
                }
            }
        }

        // Lógica para asociar documentos existentes
        if ($request->filled('factura_ids')) {
            Documento::whereIn('id', $request->factura_ids)
                ->update([
                    'documentable_id'   => $model->id,
                    'documentable_type' => $documentableType,
                ]);
        }

        if ($request->filled('garantia_ids')) {
            Documento::whereIn('id', $request->garantia_ids)
                ->update([
                    'documentable_id'       => $model->id,
                    'documentable_type'     => $documentableType,
                    'tiempo_garantia_meses' => $request->tiempo_garantia_meses,
                ]);
        }
    }

    // Método para descargar documentos
    public function descargarDocumento($id)
    {
        $documento = Documento::findOrFail($id);
        
        // Asumiendo que 'ruta_s3' contiene la ruta de almacenamiento (e.g., documentos/archivo.pdf)
        $rutaCompleta = $documento->ruta_s3; 

        // ✅ CORRECCIÓN: Especificar el disco 'public'
        if (!Storage::disk('public')->exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no se encontró en el servidor.');
        }
        
        $nombreDescarga = $documento->tipo . '-' . $documento->nombre_archivo;

        // ✅ CORRECCIÓN: Especificar el disco 'public'
        return Storage::disk('public')->download($rutaCompleta, $nombreDescarga);
    }
    
    // Método para buscar con IA (Gemini)
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

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        $prompt = "Actúa como un experto en inventario de TI. Busca en la web el modelo de equipo asociado al número de serie o fragmento de serie: '{$serialNumber}'. Es muy común que un fragmento de serie esté asociado a varios modelos. Responde ÚNICAMENTE con un objeto JSON que contenga un array de posibles modelos bajo la clave 'opciones_modelos'. Cada elemento del array debe ser un objeto con las siguientes claves: 'marca', 'modelo', 'tipo_equipo_nombre' (ej: Notebook), 'precio' (valor numérico estimado) y 'especificaciones_clave' (string de resumen). Si solo encuentras un resultado claro, devuelve un array de un solo elemento. Si no encuentras nada, devuelve un array vacío. Asegúrate que el precio sea numérico.";
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

            if ($response->successful()) {
                $content = $response->json();
                $iaResponseText = trim($content['candidates'][0]['content']['parts'][0]['text'] ?? '');
                $data = json_decode($iaResponseText, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($data['opciones_modelos'])) {
                    $opciones = $data['opciones_modelos'];
                    
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
