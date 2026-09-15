@extends('layout')

@section('title', 'Reportes y Graficos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-chart-line me-2 text-gradient"></i> Reportes y Graficos</h4>
        <small class="text-muted">Genera reportes detallados del rendimiento de tus contenidos</small>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-content">
            <div class="card-header">
                <i class="fa-solid fa-sliders me-2 text-gradient"></i> Configuracion del Reporte
            </div>
            <div class="card-body">
                <form action="{{ route('reportes.generar') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-calendar me-1" style="color:#bc1888"></i> Rango de Fechas <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="fecha_desde" value="{{ old('fecha_desde', now()->startOfMonth()->format('Y-m-d')) }}"
                                    class="form-control @error('fecha_desde') is-invalid @enderror" required>
                                <small class="text-muted">Desde</small>
                            </div>
                            <div class="col-6">
                                <input type="date" name="fecha_hasta" value="{{ old('fecha_hasta', now()->format('Y-m-d')) }}"
                                    class="form-control @error('fecha_hasta') is-invalid @enderror" required>
                                <small class="text-muted">Hasta</small>
                            </div>
                        </div>
                        @error('fecha_desde') <small class="text-danger">{{ $message }}</small> @enderror
                        @error('fecha_hasta') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-share-nodes me-1" style="color:#bc1888"></i> Red Social</label>
                        <select name="red_social_id" class="form-select">
                            <option value="">Todas las redes</option>
                            @foreach($redesSociales as $red)
                                <option value="{{ $red->id }}" {{ old('red_social_id') == $red->id ? 'selected' : '' }}>
                                    {{ $red->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(session('usuario_rol') === 'admin')
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-user me-1" style="color:#bc1888"></i> Usuario</label>
                            <select name="usuario_filtro" class="form-select">
                                <option value="">Todos los usuarios</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ old('usuario_filtro') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->nombre }} ({{ $usuario->rol }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-tag me-1" style="color:#bc1888"></i> Tipo de Contenido</label>
                        <select name="tipo_contenido" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach(['imagen','video','texto','stories','reel','live','podcast'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_contenido') == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-circle-info me-1" style="color:#bc1888"></i> Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            <option value="borrador" {{ old('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-soc w-100 py-2">
                        <i class="fa-solid fa-file-chart-line me-1"></i> Generar Reporte
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-content mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb me-2 text-warning"></i> Consejos</h6>
                <ul class="small text-muted mb-0 ps-3" style="line-height:1.9">
                    <li>Selecciona un rango de fechas corto para analisis detallados.</li>
                    <li>Compara el rendimiento entre distintas redes sociales.</li>
                    <li>Revisa el top de contenidos para replicar estrategias exitosas.</li>
                    <li>Los graficos se actualizan automaticamente con cada busqueda.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-content h-100">
            <div class="card-header">
                <i class="fa-solid fa-chart-simple me-2 text-gradient"></i> Previsualizacion de Reportes
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded-3" style="background:#fee2e2">
                            <i class="fa-solid fa-eye fa-lg mb-2" style="color:#dc2743"></i>
                            <h5 class="fw-bold mb-0">--</h5>
                            <small class="text-muted">Total Vistas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded-3" style="background:#dbeafe">
                            <i class="fa-solid fa-heart fa-lg mb-2" style="color:#1877F2"></i>
                            <h5 class="fw-bold mb-0">--</h5>
                            <small class="text-muted">Total Likes</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded-3" style="background:#d1fae5">
                            <i class="fa-solid fa-comment-dots fa-lg mb-2" style="color:#25D366"></i>
                            <h5 class="fw-bold mb-0">--</h5>
                            <small class="text-muted">Comentarios</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded-3" style="background:#fef3c7">
                            <i class="fa-solid fa-share-nodes fa-lg mb-2" style="color:#d97706"></i>
                            <h5 class="fw-bold mb-0">--</h5>
                            <small class="text-muted">Compartidos</small>
                        </div>
                    </div>
                </div>

                <div class="empty-state mt-4">
                    <canvas id="chartVacio" height="100"></canvas>
                    <p class="text-muted mt-3 mb-0">
                        <i class="fa-solid fa-arrow-up-from-bracket me-2"></i>
                        Configura los filtros a la izquierda y pulsa <strong>"Generar Reporte"</strong> para visualizar estadisticas detalladas, graficos interactivos y tablas de datos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
new Chart(document.getElementById('chartVacio'), {
    type: 'line',
    data: {
        labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul'],
        datasets: [{
            label: 'Vistas',
            data: [0,0,0,0,0,0,0],
            borderColor: 'rgba(220,39,67,.15)',
            backgroundColor: 'rgba(220,39,67,.04)',
            fill: true,
            tension: .4,
            borderWidth: 2,
            pointBackgroundColor: 'rgba(220,39,67,.3)'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { display: false } },
            x: { grid: { display: false }, ticks: { color: '#ccc' } }
        }
    }
});
</script>
@endsection