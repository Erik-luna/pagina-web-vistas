@extends('layout')

@section('title', 'Editar Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('usuarios.index') }}" class="btn btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-user-pen me-2 text-gradient"></i> Editar Usuario</h4>
                <small class="text-muted">Actualiza la informacion del usuario</small>
            </div>
        </div>

        <div class="card card-content">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ $usuario->avatar }}" class="rounded-circle shadow" style="width:80px;height:80px" alt="">
                    <h5 class="fw-bold mt-2 mb-0">{{ $usuario->nombre }}</h5>
                    <small class="text-muted">{{ $usuario->email }}</small>
                </div>

                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required>
                        @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo electronico <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nueva contraseña <small class="text-muted fw-normal">(dejar vacio para mantener la actual)</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select @error('rol') is-invalid @enderror" required>
                                <option value="cliente" {{ old('rol', $usuario->rol) == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                <option value="admin" {{ old('rol', $usuario->rol) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('rol') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Telefono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="form-control">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Estado de la cuenta <span class="text-danger">*</span></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch" {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activoSwitch">Cuenta activa</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-2 pt-3 border-top">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary-soc px-5"><i class="fa-solid fa-save me-1"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection