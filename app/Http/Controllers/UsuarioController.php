<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::withCount('registros');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $usuarios = $query->latest()->paginate(10)->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:admin,cliente',
            'telefono' => 'nullable|string|max:20',
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => $request->password,
            'rol' => $request->rol,
            'telefono' => $request->telefono,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->nombre) . '&background=6366f1&color=fff&size=128',
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email,' . $id,
            'rol' => 'required|in:admin,cliente',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'required|boolean',
        ]);

        $data = $request->only(['nombre', 'email', 'rol', 'telefono', 'activo']);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id == session()->get('usuario_id')) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }

    public function toggle($id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id == session()->get('usuario_id')) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';
        return redirect()->route('usuarios.index')->with('success', "Usuario {$estado} exitosamente.");
    }
}
