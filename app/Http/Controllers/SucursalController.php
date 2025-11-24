<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $query = Sucursal::query();

        if ($search = $request->get('search')) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
        }

        $sucursales = $query->paginate(10);

        return view('gestion_sucursales', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $sucursal = Sucursal::create([
            'nombre'        => $request->nombre,
            'direccion'     => $request->direccion,
            'telefono'      => $request->telefono,
            'fecha_registro'=> now(),
        ]);

        $sucursal->registrarMovimiento('Registro de Sucursal', 'Sucursal creada en el sistema');

        return redirect()->route('gestion_sucursales')->with('success', 'Sucursal registrada correctamente.');
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $sucursal->update($request->only(['nombre','direccion','telefono']));

        $sucursal->registrarMovimiento('Actualización de Sucursal', 'Sucursal actualizada');

        return redirect()->route('gestion_sucursales')->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();

        return redirect()->route('gestion_sucursales')->with('success', 'Sucursal eliminada correctamente.');
    }

    // ✅ Autocomplete para buscador
    public function autocomplete(Request $request)
    {
        $termino = $request->get('term');

        $resultados = Sucursal::where('nombre', 'LIKE', '%' . $termino . '%')
            ->select('nombre')
            ->distinct()
            ->take(10)
            ->get()
            ->pluck('nombre');

        return response()->json($resultados);
    }
}