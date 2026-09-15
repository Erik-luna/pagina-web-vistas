@extends('layout')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('usuarios.index') }}" class="btn btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-user-plus me-2 text-gradient"></i> Nuevo Usuario</h4>
                <small class="text-muted">Crea una cuenta de administrador o cliente</small>
            </div>
        </div>

        <div class="card card-content">
            <div class="card-body p-4">
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control @error('nombre') is-invalid @enderror" required>
                        @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo electronico <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Confirmar contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select @error('rol') is-invalid @enderror" required>
                                <option value="cliente" {{ old('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('rol') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Telefono</label>
                            <input type="text" name="telefono" value="{{ old('telefono') }}" class="form-control">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-2 pt-3 border-top">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary-soc px-5"><i class="fa-solid fa-check me-1"></i> Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection