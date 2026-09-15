<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\RedSocial;
use App\Models\RegistroVista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroVistaController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $query = RegistroVista::with(['usuario', 'redSocial']);

        if ($rol !== 'admin') {
            $query->where('usuario_id', $usuarioId);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('red_social_id')) {
            $query->where('red_social_id', $request->red_social_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_contenido')) {
            $query->where('tipo_contenido', $request->tipo_contenido);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_registro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_registro', '<=', $request->fecha_hasta);
        }

        $registros = $query->latest('fecha_registro')->paginate(12)->withQueryString();
        $redesSociales = RedSocial::where('activa', true)->get();

        return view('registros.index', compact('registros', 'redesSociales'));
    }

    public function create()
    {
        $redesSociales = RedSocial::where('activa', true)->get();
        return view('registros.create', compact('redesSociales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'red_social_id' => 'required|exists:redes_sociales,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'vistas' => 'required|integer|min:0',
            'likes' => 'required|integer|min:0',
            'comentarios' => 'required|integer|min:0',
            'compartidos' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo,borrador',
            'tipo_contenido' => 'required|in:imagen,video,texto,stories,reel,live,podcast',
            'url_contenido' => 'nullable|url|max:500',
            'fecha_registro' => 'required|date',
        ]);

        $registro = RegistroVista::create([
            'usuario_id' => session()->get('usuario_id'),
            'red_social_id' => $request->red_social_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'vistas' => $request->vistas,
            'likes' => $request->likes,
            'comentarios' => $request->comentarios,
            'compartidos' => $request->compartidos,
            'estado' => $request->estado,
            'tipo_contenido' => $request->tipo_contenido,
            'url_contenido' => $request->url_contenido,
            'fecha_registro' => $request->fecha_registro,
        ]);

        if (session()->get('usuario_rol') === 'admin') {
            Notificacion::create([
                'usuario_id' => session()->get('usuario_id'),
                'titulo' => 'Nuevo registro creado',
                'mensaje' => "Se ha registrado el contenido \"{$registro->titulo}\" exitosamente.",
                'tipo' => 'exito',
            ]);
        }

        return redirect()->route('registros.index')->with('success', 'Registro creado exitosamente.');
    }

    public function edit($id)
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $registro = RegistroVista::findOrFail($id);

        if ($rol !== 'admin' && $registro->usuario_id !== $usuarioId) {
            return redirect()->route('registros.index')->with('error', 'No tienes permiso para editar este registro.');
        }

        $redesSociales = RedSocial::where('activa', true)->get();
        return view('registros.edit', compact('registro', 'redesSociales'));
    }

    public function update(Request $request, $id)
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $registro = RegistroVista::findOrFail($id);

        if ($rol !== 'admin' && $registro->usuario_id !== $usuarioId) {
            return redirect()->route('registros.index')->with('error', 'No tienes permiso para editar este registro.');
        }

        $request->validate([
            'red_social_id' => 'required|exists:redes_sociales,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'vistas' => 'required|integer|min:0',
            'likes' => 'required|integer|min:0',
            'comentarios' => 'required|integer|min:0',
            'compartidos' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo,borrador',
            'tipo_contenido' => 'required|in:imagen,video,texto,stories,reel,live,podcast',
            'url_contenido' => 'nullable|url|max:500',
            'fecha_registro' => 'required|date',
        ]);

        $registro->update($request->only([
            'red_social_id', 'titulo', 'descripcion', 'vistas', 'likes',
            'comentarios', 'compartidos', 'estado', 'tipo_contenido',
            'url_contenido', 'fecha_registro',
        ]));

        return redirect()->route('registros.index')->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $registro = RegistroVista::findOrFail($id);

        if ($rol !== 'admin' && $registro->usuario_id !== $usuarioId) {
            return redirect()->route('registros.index')->with('error', 'No tienes permiso para eliminar este registro.');
        }

        $registro->delete();

        return redirect()->route('registros.index')->with('success', 'Registro eliminado exitosamente.');
    }

    public function show($id)
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $registro = RegistroVista::with(['usuario', 'redSocial'])->findOrFail($id);

        if ($rol !== 'admin' && $registro->usuario_id !== $usuarioId) {
            return redirect()->route('registros.index')->with('error', 'No tienes permiso para ver este registro.');
        }

        return view('registros.show', compact('registro'));
    }
}
