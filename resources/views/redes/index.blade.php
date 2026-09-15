@extends('layout')

@section('title', 'Redes Sociales')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-share-nodes me-2 text-gradient"></i> Redes Sociales</h4>
        <small class="text-muted">Gestiona las plataformas disponibles para registrar contenido</small>
    </div>
    <a href="{{ route('redes.create') }}" class="btn btn-primary-soc">
        <i class="fa-solid fa-plus me-1"></i> Nueva Red Social
    </a>
</div>

<div class="row g-3">
    @forelse($redes as $red)
        <div class="col-md-6 col-xl-4">
            <div class="card card-content h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:{{ $red->color }};color:#fff;font-size:1.6rem;flex-shrink:0">
                            <i class="fa-brands fa-{{ $red->icono }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0">{{ $red->nombre }}</h5>
                            <small class="text-muted">{{ $red->registros_count }} registros registrados</small>
                        </div>
                        @if($red->activa)
                            <span class="badge rounded-pill bg-success-subtle text-success"><i class="fa-solid fa-circle me-1" style="font-size:.5rem"></i> Activa</span>
                        @else
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary"><i class="fa-solid fa-circle me-1" style="font-size:.5rem"></i> Inactiva</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small">
                            <i class="fa-solid fa-tag me-1 text-muted"></i> Color:
                            <span class="fw-semibold">{{ $red->color }}</span>
                        </div>
                        <div class="small">
                            <i class="fa-solid fa-code me-1 text-muted"></i> Icono:
                            <code>fa-{{ $red->icono }}</code>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('redes.edit', $red->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                            <i class="fa-solid fa-pen me-1"></i> Editar
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" onclick="confirmarEliminar({{ $red->id }}, '{{ $red->nombre }}')">
                            <i class="fa-solid fa-trash me-1"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
            <form id="form-eliminar-{{ $red->id }}" action="{{ route('redes.destroy', $red->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-content">
                <div class="empty-state">
                    <i class="fa-solid fa-share-nodes"></i>
                    <h6 class="fw-bold mt-3">No hay redes sociales registradas</h6>
                    <p class="text-muted">Agrega la primera red social para comenzar</p>
                    <a href="{{ route('redes.create') }}" class="btn btn-primary-soc">Agregar Red Social</a>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
function confirmarEliminar(id, nombre) {
    if (confirm(`¿Seguro que deseas eliminar la red social "${nombre}"? Se eliminaran todos sus registros asociados.`)) {
        document.getElementById('form-eliminar-' + id).submit();
    }
}
</script>
@endsection