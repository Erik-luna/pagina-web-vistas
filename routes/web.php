<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\RedSocialController;
use App\Http\Controllers\RegistroVistaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use App\Http\Controllers\Auth\GoogleController; // O el controlador donde pongas la lógica

use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

// Redirige a Google
Route::get('auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.login');

// Recibe la respuesta de Google
Route::get('auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();

        // Buscamos si el usuario ya existe en tu tabla 'usuarios' por su correo
        $usuario = Usuario::where('email', $googleUser->getEmail())->first();

        if (!$usuario) {
            // Si no existe, lo creamos automáticamente
            $usuario = Usuario::create([
                'nombre' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // Contraseña aleatoria segura
                'rol' => 'cliente',
                'avatar' => $googleUser->getAvatar(),
                'activo' => true,
            ]);
        }

        // Si usas sesiones tradicionales de tu proyecto actual:
        session()->put([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'usuario_avatar' => $usuario->avatar,
        ]);

        return redirect()->route('dashboard')->with('success', "¡Bienvenido/a, {$usuario->nombre}!");

    } catch (\Exception $e) {
        return redirect()->route('login')->with('error', 'Hubo un error al iniciar sesión con Google.');
    }
});

Route::get('/', function () {
    if (session()->has('usuario_id')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
Route::post('/registro', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('registros', RegistroVistaController::class);

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::post('/reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');
    Route::get('/reportes/exportar/{formato}', [ReporteController::class, 'exportar'])->name('reportes.exportar');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/todas-leer', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.todasLeer');

    Route::middleware(['rol:admin'])->prefix('admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::post('usuarios/{id}/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
        Route::resource('redes', RedSocialController::class)->except(['show']);
    });
});