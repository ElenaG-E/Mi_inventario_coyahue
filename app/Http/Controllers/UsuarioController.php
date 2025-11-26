<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with('rol');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhereHas('rol', function ($qr) use ($search) {
                        $qr->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhere('estado', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->paginate(10);
        $roles = Rol::all();

        return view('gestion_usuarios', compact('usuarios', 'roles'));
    }

    public function show(Usuario $usuario)
    {
        $usuario->load([
            'rol',
            'asignaciones' => function ($query) {
                $query->with([
                    'equipo.tipoEquipo',
                    'equipo.estadoEquipo',
                    'equipo.sucursal',
                ])->orderByDesc('fecha_asignacion');
            },
        ]);

        $historialAsignaciones = $usuario->asignaciones;
        $equiposAsignados = $historialAsignaciones->filter->isActiva()->values();

        return view('detalle_usuario', [
            'usuario' => $usuario,
            'equiposAsignados' => $equiposAsignados,
            'historialAsignaciones' => $historialAsignaciones,
        ]);
    }

    public function store(Request $request)
    {
        // Todos los campos obligatorios al crear
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'required|string|max:20',
            'rol_id' => 'required|integer|exists:roles,id',
            'estado' => 'required|string|in:activo,inactivo',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // minúscula
                'regex:/[A-Z]/',      // mayúscula
                'regex:/[0-9]/',      // número
                'regex:/[\W]/',       // símbolo
            ],
            'password_confirmation' => 'required|same:password',
        ], [
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',
        ]);

        $data = $request->only(['nombre', 'email', 'telefono', 'rol_id', 'estado', 'password']);
        $data['password'] = Hash::make($data['password']);

        Usuario::create($data);

        return redirect()->route('gestion_usuarios')->with('success', 'Usuario agregado correctamente');
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Contraseña opcional al editar
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . $usuario->id,
            'telefono' => 'required|string|max:20',
            'rol_id' => 'required|integer|exists:roles,id',
            'estado' => 'required|string|in:activo,inactivo',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W]/',
            ],
            'password_confirmation' => 'nullable|same:password',
        ], [
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',
        ]);

        $data = $request->only(['nombre', 'email', 'telefono', 'rol_id', 'estado', 'password']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('gestion_usuarios')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return redirect()->route('gestion_usuarios')->with('success', 'Usuario eliminado');
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term', '');

        $resultados = Usuario::with('rol')
            ->where('nombre', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('telefono', 'like', "%{$term}%")
            ->orWhereHas('rol', function ($qr) use ($term) {
                $qr->where('nombre', 'like', "%{$term}%");
            })
            ->orWhere('estado', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        $sugerencias = [];
        foreach ($resultados as $usuario) {
            if (stripos($usuario->nombre, $term) !== false) {
                $sugerencias[] = ['label' => $usuario->nombre, 'value' => $usuario->nombre];
            } elseif (stripos($usuario->email, $term) !== false) {
                $sugerencias[] = ['label' => $usuario->email, 'value' => $usuario->email];
            } elseif (!is_null($usuario->telefono) && stripos($usuario->telefono, $term) !== false) {
                $sugerencias[] = ['label' => $usuario->telefono, 'value' => $usuario->telefono];
            } elseif ($usuario->rol && stripos($usuario->rol->nombre, $term) !== false) {
                $sugerencias[] = ['label' => $usuario->rol->nombre, 'value' => $usuario->rol->nombre];
            } elseif (stripos($usuario->estado, $term) !== false) {
                $sugerencias[] = ['label' => $usuario->estado, 'value' => $usuario->estado];
            }
        }

        return response()->json($sugerencias);
    }
}

