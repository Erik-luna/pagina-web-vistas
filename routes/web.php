<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\RedSocialController;
use App\Http\Controllers\RegistroVistaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

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
