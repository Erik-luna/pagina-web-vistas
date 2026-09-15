<?php

namespace App\Http\Controllers;

use App\Models\RedSocial;
use App\Models\RegistroVista;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        $redesSociales = RedSocial::where('activa', true)->get();
        $usuarios = Usuario::where('activo', true)->get();

        return view('reportes.index', compact('redesSociales', 'usuarios'));
    }

    public function generar(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        $query = RegistroVista::with(['usuario', 'redSocial']);

        if ($rol !== 'admin') {
            $query->where('usuario_id', $usuarioId);
        }

        $query->whereBetween('fecha_registro', [$request->fecha_desde, $request->fecha_hasta]);

        if ($request->filled('red_social_id')) {
            $query->where('red_social_id', $request->red_social_id);
        }

        if ($request->filled('usuario_filtro') && $rol === 'admin') {
            $query->where('usuario_id', $request->usuario_filtro);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_contenido')) {
            $query->where('tipo_contenido', $request->tipo_contenido);
        }

        $registros = $query->orderBy('fecha_registro', 'desc')->get();

        $resumen = [
            'total_registros' => $registros->count(),
            'total_vistas' => $registros->sum('vistas'),
            'total_likes' => $registros->sum('likes'),
            'total_comentarios' => $registros->sum('comentarios'),
            'total_compartidos' => $registros->sum('compartidos'),
            'promedio_vistas' => $registros->count() > 0 ? round($registros->sum('vistas') / $registros->count()) : 0,
            'mejor_registro' => $registros->sortByDesc('vistas')->first(),
            'por_red' => $registros->groupBy('red_social_id')->map(function ($group) {
                return [
                    'nombre' => $group->first()->redSocial->nombre ?? 'N/A',
                    'color' => $group->first()->redSocial->color ?? '#6366f1',
                    'total' => $group->count(),
                    'vistas' => $group->sum('vistas'),
                    'likes' => $group->sum('likes'),
                ];
            })->values(),
            'por_tipo' => $registros->groupBy('tipo_contenido')->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'vistas' => $group->sum('vistas'),
                ];
            }),
            'por_estado' => $registros->groupBy('estado')->map(function ($group) {
                return $group->count();
            }),
            'por_dia' => $registros->groupBy(function ($item) {
                return $item->fecha_registro->format('Y-m-d');
            })->map(function ($group) {
                return [
                    'vistas' => $group->sum('vistas'),
                    'likes' => $group->sum('likes'),
                    'registros' => $group->count(),
                ];
            })->sortKeys(),
            'por_usuario' => $rol === 'admin' ? $registros->groupBy('usuario_id')->map(function ($group) {
                return [
                    'nombre' => $group->first()->usuario->nombre ?? 'N/A',
                    'total' => $group->count(),
                    'vistas' => $group->sum('vistas'),
                ];
            })->values() : collect(),
        ];

        $redesSociales = RedSocial::where('activa', true)->get();
        $usuarios = Usuario::where('activo', true)->get();

        return view('reportes.resultado', compact('registros', 'resumen', 'redesSociales', 'usuarios'));
    }

    public function exportar(Request $request, string $formato)
    {
        // Placeholder for export functionality
        return redirect()->route('reportes.index')->with('info', "Exportacion en {$formato} proximamente disponible.");
    }
}
