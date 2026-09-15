<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\RedSocial;
use App\Models\RegistroVista;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioId = session()->get('usuario_id');
        $rol = session()->get('usuario_rol');

        if ($rol === 'admin') {
            return $this->dashboardAdmin();
        }

        return $this->dashboardCliente($usuarioId);
    }

    private function dashboardAdmin()
    {
        $totalRegistros = RegistroVista::count();
        $totalUsuarios = Usuario::count();
        $totalVistas = RegistroVista::sum('vistas');
        $totalLikes = RegistroVista::sum('likes');
        $totalComentarios = RegistroVista::sum('comentarios');
        $totalCompartidos = RegistroVista::sum('compartidos');
        $registrosActivos = RegistroVista::where('estado', 'activo')->count();
        $registrosHoy = RegistroVista::whereDate('fecha_registro', today())->count();

        $registrosPorRed = RedSocial::withCount('registros')
            ->with(['registros' => function ($q) {
                $q->select('red_social_id', DB::raw('SUM(vistas) as total_vistas'), DB::raw('SUM(likes) as total_likes'));
                $q->groupBy('red_social_id');
            }])
            ->where('activa', true)
            ->get();

        $registrosRecientes = RegistroVista::with(['usuario', 'redSocial'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $topContenido = RegistroVista::with(['usuario', 'redSocial'])
            ->where('estado', 'activo')
            ->orderByDesc('vistas')
            ->take(5)
            ->get();

        $usuariosTop = Usuario::withCount('registros')
            ->with(['registros' => function ($q) {
                $q->select('usuario_id', DB::raw('SUM(vistas) as total_vistas'));
                $q->groupBy('usuario_id');
            }])
            ->orderByDesc(DB::raw('(SELECT SUM(vistas) FROM registros_vistas WHERE usuario_id = usuarios.id)'))
            ->take(5)
            ->get();

        $vistasPorMes = RegistroVista::select(
            DB::raw('MONTH(fecha_registro) as mes'),
            DB::raw('YEAR(fecha_registro) as anio'),
            DB::raw('SUM(vistas) as total_vistas'),
            DB::raw('SUM(likes) as total_likes'),
            DB::raw('COUNT(*) as total_registros')
        )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->take(12)
            ->get();

        $notificaciones = Notificacion::where('usuario_id', session()->get('usuario_id'))
            ->where('leida', false)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalRegistros', 'totalUsuarios', 'totalVistas', 'totalLikes',
            'totalComentarios', 'totalCompartidos', 'registrosActivos', 'registrosHoy',
            'registrosPorRed', 'registrosRecientes', 'topContenido', 'usuariosTop',
            'vistasPorMes', 'notificaciones'
        ));
    }

    private function dashboardCliente($usuarioId)
    {
        $misRegistros = RegistroVista::where('usuario_id', $usuarioId)->count();
        $misVistas = RegistroVista::where('usuario_id', $usuarioId)->sum('vistas');
        $misLikes = RegistroVista::where('usuario_id', $usuarioId)->sum('likes');
        $misComentarios = RegistroVista::where('usuario_id', $usuarioId)->sum('comentarios');
        $misCompartidos = RegistroVista::where('usuario_id', $usuarioId)->sum('compartidos');
        $registrosActivos = RegistroVista::where('usuario_id', $usuarioId)->where('estado', 'activo')->count();

        $registrosRecientes = RegistroVista::with('redSocial')
            ->where('usuario_id', $usuarioId)
            ->latest('created_at')
            ->take(8)
            ->get();

        $misRegistrosPorRed = RedSocial::withCount(['registros' => function ($q) use ($usuarioId) {
            $q->where('usuario_id', $usuarioId);
        }])
            ->where('activa', true)
            ->get()
            ->filter(function ($item) {
                return $item->registros_count > 0;
            });

        $vistasPorMes = RegistroVista::select(
            DB::raw('MONTH(fecha_registro) as mes'),
            DB::raw('YEAR(fecha_registro) as anio'),
            DB::raw('SUM(vistas) as total_vistas'),
            DB::raw('SUM(likes) as total_likes'),
            DB::raw('COUNT(*) as total_registros')
        )
            ->where('usuario_id', $usuarioId)
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->take(12)
            ->get();

        $notificaciones = Notificacion::where('usuario_id', $usuarioId)
            ->where('leida', false)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.cliente', compact(
            'misRegistros', 'misVistas', 'misLikes', 'misComentarios',
            'misCompartidos', 'registrosActivos', 'registrosRecientes',
            'misRegistrosPorRed', 'vistasPorMes', 'notificaciones'
        ));
    }
}
