<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Mostrar listado de proveedores con buscador.
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('rut', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
            });
        }

        $proveedores = $query->paginate(10);

        return view('gestion_proveedores', compact('proveedores'));
    }

    /**
     * Guardar un nuevo proveedor.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        Proveedor::create($request->all());

        return redirect()->route('gestion_proveedores')
                         ->with('success', 'Proveedor agregado correctamente');
    }

    /**
     * Actualizar un proveedor existente.
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'rut' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        $proveedor->update($request->all());

        return redirect()->route('gestion_proveedores')
                         ->with('success', 'Proveedor actualizado correctamente');
    }

    /**
     * Eliminar un proveedor.
     */
    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return redirect()->route('gestion_proveedores')
                         ->with('success', 'Proveedor eliminado');
    }

    /**
     * Endpoint para autocompletado del buscador.
     */
public function autocomplete(Request $request)
{
    $term = $request->get('term');

    $resultados = Proveedor::where('rut', 'like', "%{$term}%")
        ->orWhere('nombre', 'like', "%{$term}%")
        ->orWhere('telefono', 'like', "%{$term}%")
        ->orWhere('correo', 'like', "%{$term}%")
        ->orWhere('direccion', 'like', "%{$term}%")
        ->limit(10)
        ->get();

    $sugerencias = [];

    foreach ($resultados as $proveedor) {
        // Rut
        if (stripos($proveedor->rut, $term) !== false) {
            $sugerencias[] = ['label' => $proveedor->rut, 'value' => $proveedor->rut];
        }
        // Nombre
        elseif (stripos($proveedor->nombre, $term) !== false) {
            $sugerencias[] = ['label' => $proveedor->nombre, 'value' => $proveedor->nombre];
        }
        // Teléfono
        elseif (stripos($proveedor->telefono, $term) !== false) {
            $sugerencias[] = ['label' => $proveedor->telefono, 'value' => $proveedor->telefono];
        }
        // Correo
        elseif (stripos($proveedor->correo, $term) !== false) {
            $sugerencias[] = ['label' => $proveedor->correo, 'value' => $proveedor->correo];
        }
        // Dirección
        elseif (stripos($proveedor->direccion, $term) !== false) {
            $sugerencias[] = ['label' => $proveedor->direccion, 'value' => $proveedor->direccion];
        }
    }

    return response()->json($sugerencias);
}
}