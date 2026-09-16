<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->where('activo', true)->first();

        if (!$usuario || !$this->verificarPassword($usuario, $request->password)) {
            return back()->withInput($request->only('email'))->with('error', 'Credenciales incorrectas.');
        }

        session()->put([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'usuario_avatar' => $usuario->avatar,
        ]);

        return redirect()->route('dashboard')->with('success', "Bienvenido/a, {$usuario->nombre}!");
    }

    private function verificarPassword(Usuario $usuario, string $password): bool
    {
        try {
            if (Hash::check($password, $usuario->password)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Contraseña almacenada sin formato bcrypt (texto plano).
        }

        // Compatibilidad con contraseñas guardadas en texto plano. Al acertar
        // se guarda en bcrypt gracias al cast 'hashed' del modelo.
        if (hash_equals((string) $usuario->password, (string) $password)) {
            try {
                $usuario->password = $password;
                $usuario->save();
            } catch (\Throwable $e) {
                // no bloquea el login
            }
            return true;
        }
        return false;
    }

    public function showRegister()
    {
        if (session()->has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:cliente,admin',
            'telefono' => 'nullable|string|max:20',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => $request->password,
            'rol' => $request->rol,
            'telefono' => $request->telefono,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->nombre) . '&background=6366f1&color=fff&size=128',
        ]);

        session()->put([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'usuario_avatar' => $usuario->avatar,
        ]);

        return redirect()->route('dashboard')->with('success', 'Cuenta creada exitosamente. Bienvenido/a!');
    }

    public function logout()
    {
        session()->forget(['usuario_id', 'usuario_nombre', 'usuario_rol', 'usuario_avatar']);
        session()->invalidate();
        return redirect()->route('login')->with('success', 'Sesion cerrada correctamente.');
    }
}
