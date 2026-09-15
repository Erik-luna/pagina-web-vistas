@extends('layout')

@section('title', 'Registros de Vistas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list me-2 text-gradient"></i> Registros de Vistas</h4>
        <small class="text-muted">Gestiona todas las metricas de tus contenidos en redes sociales</small>
    </div>
    <a href="{{ route('registros.create') }}" class="btn btn-primary-soc">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Registro
    </a>
</div>

<div class="card card-content mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('registros.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Titulo o descripcion...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Red Social</label>
                <select name="red_social_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($redesSociales as $red)
                        <option value="{{ $red->id }}" {{ request('red_social_id') == $red->id ? 'selected' : '' }}>
                            {{ $red->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="tipo_contenido" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['imagen','video','texto','stories','reel','live','podcast'] as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo_contenido') == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary-soc btn-sm px-4">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('registros.index') }}" class="btn btn-light btn-sm px-4 border">
                    <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
                </a>
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
                        <th class="ps-4">ID</th>
                        <th>Contenido</th>
                        <th>Red Social</th>
                        <th>Tipo</th>
                        <th class="text-center">Vistas</th>
                        <th class="text-center">Likes</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha</th>
                        @if(request()->routeIs('registros.index') && session('usuario_rol') === 'admin')
                            <th>Usuario</th>
                        @endif
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $registro)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">#{{ $registro->id }}</td>
                            <td>
                                <strong class="d-block" style="font-size:.88rem">{{ Str::limit($registro->titulo, 35) }}</strong>
                                <small class="text-muted">
                                    <i class="fa-solid fa-comment-dots me-1"></i>{{ $registro->comentarios }} comentarios
                                    · <i class="fa-solid fa-share-nodes me-1"></i>{{ $registro->compartidos }} compartidos
                                </small>
                            </td>
                            <td>
                                <span class="social-badge" style="background:{{ $registro->redSocial->color }}">
                                    <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i> {{ $registro->redSocial->nombre }}
                                </span>
                            </td>
                            <td>
                                @if($registro->tipo_contenido == 'video') <i class="fa-solid fa-video content-type-icon" style="color:#dc2743" title="Video"></i>
                                @elseif($registro->tipo_contenido == 'reel') <i class="fa-solid fa-clapperboard content-type-icon" style="color:#bc1888" title="Reel"></i>
                                @elseif($registro->tipo_contenido == 'stories') <i class="fa-regular fa-circle-dot content-type-icon" style="color:#f09433" title="Stories"></i>
                                @elseif($registro->tipo_contenido == 'live') <i class="fa-solid fa-tower-broadcast content-type-icon" style="color:#dc2743" title="Live"></i>
                                @elseif($registro->tipo_contenido == 'podcast') <i class="fa-solid fa-podcast content-type-icon" style="color:#6d28d9" title="Podcast"></i>
                                @elseif($registro->tipo_contenido == 'texto') <i class="fa-solid fa-file-lines content-type-icon" style="color:#1877F2" title="Texto"></i>
                                @else <i class="fa-solid fa-image content-type-icon" style="color:#25D366" title="Imagen"></i>
                                @endif
                                <small class="d-block text-muted mt-1">{{ ucfirst($registro->tipo_contenido) }}</small>
                            </td>
                            <td class="text-center fw-bold" style="color:#dc2743"><i class="fa-solid fa-eye me-1"></i>{{ number_format($registro->vistas) }}</td>
                            <td class="text-center fw-bold" style="color:#1877F2"><i class="fa-solid fa-heart me-1"></i>{{ number_format($registro->likes) }}</td>
                            <td class="text-center">
                                <span class="badge-estado {{ $registro->estado == 'activo' ? 'bg-success-subtle text-success' : ($registro->estado == 'inactivo' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
                                    {{ ucfirst($registro->estado) }}
                                </span>
                            </td>
                            <td>
                                <strong class="d-block" style="font-size:.82rem">{{ $registro->fecha_registro->format('d/m/Y') }}</strong>
                                <small class="text-muted">{{ $registro->fecha_registro->format('h:i A') }}</small>
                            </td>
                            @if(session('usuario_rol') === 'admin')
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $registro->usuario->avatar }}" class="rounded-circle" style="width:28px;height:28px" alt="">
                                        <small class="fw-semibold">{{ $registro->usuario->nombre }}</small>
                                    </div>
                                </td>
                            @endif
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('registros.edit', $registro->id) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Eliminar"
                                        onclick="confirmarEliminar({{ $registro->id }}, '{{ $registro->titulo }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                <form id="form-eliminar-{{ $registro->id }}" action="{{ route('registros.destroy', $registro->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa-solid fa-inbox"></i>
                                    <h6 class="mt-2 fw-bold">No se encontraron registros</h6>
                                    <p class="text-muted">Prueba con otros filtros o crea un nuevo registro</p>
                                    <a href="{{ route('registros.create') }}" class="btn btn-primary-soc">
                                        <i class="fa-solid fa-plus me-1"></i> Nuevo Registro
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($registros->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $registros->links() }}
        </div>
    @endif
</div>

<form id="form-eliminar" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
function confirmarEliminar(id, titulo) {
    if (confirm(`¿Seguro que deseas eliminar el registro "${titulo}"? Esta accion no se puede deshacer.`)) {
        const form = document.getElementById('form-eliminar-' + id);
        form.submit();
    }
}
</script>
@endsection