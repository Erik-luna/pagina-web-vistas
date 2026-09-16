@extends('layout')

@section('title', 'Iniciar Sesion')

@section('auth-content')
<div class="auth-wrapper" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);position:relative;overflow:hidden">
    <div class="auth-blob" style="width:300px;height:300px;background:#dc2743;top:-80px;left:-80px"></div>
    <div class="auth-blob" style="width:350px;height:350px;background:#1877F2;bottom:-100px;right:-80px"></div>
    <div class="auth-blob" style="width:200px;height:200px;background:#25D366;top:50%;left:60%"></div>

    <div class="auth-card" style="background:rgba(255,255,255,.97);border-radius:24px;box-shadow:0 25px 80px rgba(0,0,0,.5);width:100%;max-width:460px;padding:2.5rem;z-index:10;position:relative">
        <div class="auth-logo" style="width:84px;height:84px;border-radius:22px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:#fff;margin:0 auto 1.2rem;box-shadow:0 8px 30px rgba(220,39,67,.4)">
            <i class="fa-brands fa-hashtag"></i>
        </div>

        <h1 class="text-center fw-bolder mb-1" style="font-size:1.6rem">
            <span class="text-gradient">SocialMetrics</span>
        </h1>
        <p class="text-center text-muted mb-4" style="font-size:.9rem">Inicia sesion para gestionar tus registros de vistas en redes sociales</p>

        @if(session('success'))
            <div class="alert alert-success rounded-3 d-flex align-items-center gap-2" style="border:none">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2" style="border:none">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem">Correo electronico</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-regular fa-envelope" style="color:#bc1888"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="tucorreo@ejemplo.com" value="{{ old('email') }}" style="border-radius:0 12px 12px 0" required>
                </div>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:.85rem">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-lock" style="color:#bc1888"></i></span>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" style="border-radius:0 12px 12px 0" required>
                </div>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary-soc w-100 mb-3 py-3 fw-bold" style="border-radius:14px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border:none">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesion
            </button>
        </form>

        <div class="d-flex align-items-center gap-3 my-3">
            <hr class="flex-grow-1">
            <small class="text-muted">o continua con</small>
            <hr class="flex-grow-1">
        </div>

        <a href="{{ route('google.login') }}" class="btn-google" style="display:flex;align-items:center;justify-content:center;gap:.65rem;width:100%;padding:.8rem 1rem;margin-bottom:1.3rem;border:1px solid #d9d9d9;border-radius:14px;background:#fff;color:#3c4043;font-weight:600;text-decoration:none;box-shadow:0 2px 6px rgba(0,0,0,.08)">
            <i class="fa-brands fa-google" style="color:#4285F4"></i>
            Iniciar sesión con Google
        </a>

        <div class="social-circles" style="display:flex;justify-content:center;gap:.8rem;margin-bottom:1.3rem">
            <a href="#" class="social-circle" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;background:#1877F2;text-decoration:none"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-circle" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;background:linear-gradient(45deg,#f09433,#bc1888);text-decoration:none"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-circle" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;background:#000;text-decoration:none"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" class="social-circle" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;background:#FF0000;text-decoration:none"><i class="fa-brands fa-youtube"></i></a>
            <a href="#" class="social-circle" style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;background:#25D366;text-decoration:none"><i class="fa-brands fa-whatsapp"></i></a>
        </div>

        <p class="text-center mb-0" style="font-size:.9rem">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="fw-bold text-decoration-none text-gradient">Registrate aqui</a>
        </p>

        <div class="mt-4 pt-3 border-top text-center">
            <small class="text-muted d-block mb-2">Plataformas soportadas</small>
            <div style="display:flex;justify-content:center;gap:1rem;font-size:1.4rem;color:#adb5bd">
                <i class="fa-brands fa-instagram"></i>
                <i class="fa-brands fa-facebook"></i>
                <i class="fa-brands fa-tiktok"></i>
                <i class="fa-brands fa-youtube"></i>
                <i class="fa-brands fa-x-twitter"></i>
                <i class="fa-brands fa-whatsapp"></i>
                <i class="fa-brands fa-linkedin"></i>
                <i class="fa-brands fa-pinterest"></i>
            </div>
        </div>
    </div>
</div>
@endsection