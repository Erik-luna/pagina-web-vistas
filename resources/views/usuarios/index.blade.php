@extends('layout')

@section('title', 'Gestionar Usuarios')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-users me-2 text-gradient"></i> Gestionar Usuarios</h4>
        <small class="text-muted">Administra cuentas, roles y permisos del sistema</small>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary-soc">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
    </a>
</div>

<div class="card card-content mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Nombre o correo...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Rol</label>
                <select name="rol" class="form-select">
                    <option value="">Todos</option>
                    <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="cliente" {{ request('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="activo" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary-soc btn-sm"><i class="fa-solid fa-filter"></i></button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-light border btn-sm"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card card-content">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Usuario</th>
                        <th>Rol</th>
                        <th class="text-center">Registros</th>
                        <th class="text-center">Total Vistas</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha Alta</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr @if($usuario->id == session('usuario_id')) class="table-light" @endif>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $usuario->avatar }}" class="rounded-circle" style="width:40px;height:40px" alt="">
                                    <div>
                                        <strong class="d-block" style="font-size:.9rem">
                                            {{ $usuario->nombre }}
                                            @if($usuario->id == session('usuario_id')) <span class="badge bg-indigo">Tu</span> @endif
                                        </strong>
                                        <small class="text-muted">{{ $usuario->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $usuario->rol == 'admin' ? 'bg-danger' : 'bg-primary' }}" style="text-transform:capitalize">
                                    <i class="fa-solid fa-{{ $usuario->rol == 'admin' ? 'shield-halved' : 'user' }} me-1"></i>{{ $usuario->rol }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ $usuario->registros_count }}</td>
                            <td class="text-center">
                                <strong style="color:#dc2743"><i class="fa-solid fa-eye me-1"></i>{{ number_format($usuario->registros->sum('vistas')) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($usuario->activo)
                                    <span class="badge rounded-pill bg-success-subtle text-success"><i class="fa-solid fa-circle me-1" style="font-size:.5rem"></i> Activo</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary"><i class="fa-solid fa-circle me-1" style="font-size:.5rem"></i> Inactivo</span>
                                @endif
                            </td>
                            <td><small>{{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y') }}</small></td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                    @if($usuario->id != session('usuario_id'))
                                        <form action="{{ route('usuarios.toggle', $usuario->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="fa-solid fa-{{ $usuario->activo ? 'user-slash' : 'user-check' }}"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger" title="Eliminar" onclick="confirmarEliminar({{ $usuario->id }}, '{{ $usuario->nombre }}')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                                <form id="form-eliminar-{{ $usuario->id }}" action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa-solid fa-users-slash"></i>
                                    <p class="text-muted">No se encontraron usuarios</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usuarios->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function confirmarEliminar(id, nombre) {
    if (confirm(`¿Seguro que deseas eliminar al usuario "${nombre}"? Se eliminaran todos sus registros.`)) {
        document.getElementById('form-eliminar-' + id).submit();
    }
}
</script>
@endsection