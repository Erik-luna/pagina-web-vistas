<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = Notificacion::where('usuario_id', session()->get('usuario_id'))
            ->latest()
            ->paginate(20);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function marcarLeida($id)
    {
        $notificacion = Notificacion::where('usuario_id', session()->get('usuario_id'))->findOrFail($id);
        $notificacion->update(['leida' => true]);

        return redirect()->back()->with('success', 'Notificacion marcada como leida.');
    }

    public function marcarTodasLeidas()
    {
        Notificacion::where('usuario_id', session()->get('usuario_id'))
            ->where('leida', false)
            ->update(['leida' => true]);

        return redirect()->back()->with('success', 'Todas las notificaciones marcadas como leidas.');
    }
}
