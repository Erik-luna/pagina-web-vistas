@extends('layout')

@section('title', 'Crear Cuenta')

@section('auth-content')
<div class="auth-wrapper" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);position:relative;overflow:hidden">
    <div class="auth-blob" style="width:300px;height:300px;background:#25D366;top:-80px;left:-80px"></div>
    <div class="auth-blob" style="width:350px;height:350px;background:#dc2743;bottom:-100px;right:-80px"></div>
    <div class="auth-blob" style="width:200px;height:200px;background:#1877F2;top:40%;left:65%"></div>

    <div class="auth-card" style="background:rgba(255,255,255,.97);border-radius:24px;box-shadow:0 25px 80px rgba(0,0,0,.5);width:100%;max-width:520px;padding:2.5rem;z-index:10;position:relative">
        <div class="auth-logo" style="width:70px;height:70px;border-radius:20px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;margin:0 auto 1rem;box-shadow:0 8px 30px rgba(220,39,67,.4)">
            <i class="fa-solid fa-user-plus"></i>
        </div>

        <h1 class="text-center fw-bolder mb-1" style="font-size:1.5rem">
            <span class="text-gradient">Crear Cuenta</span>
        </h1>
        <p class="text-center text-muted mb-4" style="font-size:.88rem">Únete y empieza a registrar el rendimiento de tu contenido</p>

        @if(session('error'))
            <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2" style="border:none">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem">Nombre completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-user" style="color:#bc1888"></i></span>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Maria Perez" value="{{ old('nombre') }}" style="border-radius:0 12px 12px 0" required>
                </div>
                @error('nombre')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

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

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem">Telefono (opcional)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-phone" style="color:#bc1888"></i></span>
                    <input type="text" name="telefono" class="form-control" placeholder="+52 123 456 7890" value="{{ old('telefono') }}" style="border-radius:0 12px 12px 0">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:.85rem">Tipo de cuenta</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-user-tag" style="color:#bc1888"></i></span>
                    <select name="rol" class="form-select @error('rol') is-invalid @enderror" style="border-radius:0 12px 12px 0" required>
                        <option value="cliente" {{ old('rol', 'cliente') === 'cliente' ? 'selected' : '' }}>Cliente</option>
                        <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                </div>
                @error('rol')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-lock" style="color:#bc1888"></i></span>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimo 6 caracteres" style="border-radius:0 12px 12px 0" required>
                    </div>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem">Confirmar contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-radius:12px 0 0 12px"><i class="fa-solid fa-lock" style="color:#bc1888"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repite la contraseña" style="border-radius:0 12px 12px 0" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn w-100 mb-3 py-3 fw-bold" style="border-radius:14px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border:none;color:#fff">
                <i class="fa-solid fa-user-plus me-2"></i> Crear Cuenta
            </button>
        </form>

        <p class="text-center mb-0" style="font-size:.9rem">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="fw-bold text-decoration-none text-gradient">Inicia sesion</a>
        </p>
    </div>
</div>
@endsection