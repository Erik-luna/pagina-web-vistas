<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class AutenticarApi
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autorización requerido. Usa el encabezado "Authorization: Bearer <token>".',
                'data' => null,
            ], 401);
        }

        $token = trim(substr($header, 7));
        $fila = DB::table('api_tokens')->where('token', $token)->first();

        if (!$fila) {
            return response()->json([
                'success' => false,
                'message' => 'El token no es válido.',
                'data' => null,
            ], 401);
        }

        if ($fila->expira_en && strtotime($fila->expira_en) < time()) {
            DB::table('api_tokens')->where('id', $fila->id)->delete();
            return response()->json([
                'success' => false,
                'message' => 'El token ha expirado. Inicia sesión nuevamente.',
                'data' => null,
            ], 401);
        }

        $usuario = Usuario::find($fila->usuario_id);
        if (!$usuario || !$usuario->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada o desactivada.',
                'data' => null,
            ], 401);
        }

        DB::table('api_tokens')->where('id', $fila->id)->update(['ultimo_uso_at' => now()]);

        $request->attributes->set('api_usuario', $usuario);
        $request->attributes->set('api_token_id', $fila->id);

        return $next($request);
    }
}