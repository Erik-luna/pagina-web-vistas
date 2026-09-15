<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuarioId = session()->get('usuario_id');

        if (!$usuarioId) {
            return redirect()->route('login');
        }

        $usuario = Usuario::find($usuarioId);

        if (!$usuario || !$usuario->activo) {
            session()->forget(['usuario_id', 'usuario_nombre', 'usuario_rol', 'usuario_avatar']);
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada.');
        }

        if (!empty($roles) && !in_array($usuario->rol, $roles)) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta seccion.');
        }

        return $next($request);
    }
}
