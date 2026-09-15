@extends('layout')

@section('title', 'Mi Dashboard')

@section('content')
@php
    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $chartLabels = $vistasPorMes->map(fn($r) => $meses[$r->mes] . ' ' . $r->anio)->values();
    $chartVistas = $vistasPorMes->map(fn($r) => $r->total_vistas)->values();
    $chartLikes = $vistasPorMes->map(fn($r) => $r->total_likes)->values();
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-ig">
            <i class="fa-solid fa-eye stat-icon"></i>
            <div class="stat-value">{{ number_format($misVistas) }}</div>
            <div class="stat-label"><i class="fa-solid fa-eye me-1"></i> Mis Vistas</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-fb">
            <i class="fa-solid fa-heart stat-icon"></i>
            <div class="stat-value">{{ number_format($misLikes) }}</div>
            <div class="stat-label"><i class="fa-solid fa-heart me-1"></i> Mis Likes</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-wa">
            <i class="fa-solid fa-comment-dots stat-icon"></i>
            <div class="stat-value">{{ number_format($misComentarios) }}</div>
            <div class="stat-label"><i class="fa-solid fa-comment-dots me-1"></i> Comentarios</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-tiktok">
            <i class="fa-solid fa-share-nodes stat-icon"></i>
            <div class="stat-value">{{ number_format($misCompartidos) }}</div>
            <div class="stat-label"><i class="fa-solid fa-share-nodes me-1"></i> Compartidos</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-heart">
            <i class="fa-solid fa-clipboard-list stat-icon"></i>
            <div class="stat-value">{{ $misRegistros }}</div>
            <div class="stat-label"><i class="fa-solid fa-clipboard-list me-1"></i> Mis Registros</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card card-content" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border:none">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color:#fff">¡Hola, {{ session('usuario_nombre') }}! 👋</h4>
                    <p class="mb-0" style="color:#a0a3bd;font-size:.9rem">
                        <i class="fa-solid fa-bullhorn me-1"></i>
                        Sigue registrando tus contenidos para obtener mejores estadisticas.
                        Tienes <span style="color:#25D366">{{ $registrosActivos }}</span> contenidos activos.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('registros.create') }}" class="btn btn-primary-soc">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo Registro
                    </a>
                    <a href="{{ route('reportes.index') }}" class="btn btn-light fw-semibold" style="border-radius:10px">
                        <i class="fa-solid fa-chart-simple me-1"></i> Ver Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-area me-2 text-gradient"></i> Mi Rendimiento Mensual</div>
            <div class="card-body">
                <canvas id="chartMiRendimiento" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-pie me-2 text-gradient"></i> Mis Registros por Red</div>
            <div class="card-body">
                <canvas id="chartMisRedes" height="210"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card card-content">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clock-rotate-left me-2 text-gradient"></i> Mis Registros Recientes</span>
        <a href="{{ route('registros.index') }}" class="btn btn-sm btn-outline-soc">Ver todos</a>
    </div>
    <div class="card-body p-2">
        @forelse($registrosRecientes as $registro)
            <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                <span class="social-badge" style="background:{{ $registro->redSocial->color }}">
                    <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i>
                </span>
                <div class="flex-grow-1">
                    <strong class="d-block" style="font-size:.9rem">{{ Str::limit($registro->titulo, 55) }}</strong>
                    <small class="text-muted">{{ $registro->redSocial->nombre }} · {{ $registro->fecha_registro->format('d/m/Y') }}</small>
                </div>
                <div class="text-center me-3">
                    <strong class="d-block" style="color:#dc2743;font-size:.95rem"><i class="fa-solid fa-eye"></i> {{ number_format($registro->vistas) }}</strong>
                    <small class="text-muted"><i class="fa-solid fa-heart" style="color:#dc2743"></i> {{ number_format($registro->likes) }}</small>
                </div>
                <span class="badge-estado {{ $registro->estado == 'activo' ? 'bg-success-subtle text-success' : ($registro->estado == 'inactivo' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
                    {{ ucfirst($registro->estado) }}
                </span>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-solid fa-clipboard-list"></i>
                <h6 class="mt-2 fw-bold">Aun no tienes registros</h6>
                <p class="text-muted">Empieza registrando tu primer contenido</p>
                <a href="{{ route('registros.create') }}" class="btn btn-primary-soc">
                    <i class="fa-solid fa-plus me-1"></i> Crear primer registro
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
new Chart(document.getElementById('chartMiRendimiento'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Vistas',
                data: @json($chartVistas),
                backgroundColor: 'rgba(220,39,67,.7)',
                borderRadius: 6,
                barPercentage: .6
            },
            {
                label: 'Likes',
                data: @json($chartLikes),
                backgroundColor: 'rgba(24,119,242,.7)',
                borderRadius: 6,
                barPercentage: .6
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } },
            tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${Number(c.raw).toLocaleString('es-MX')}` } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: (v) => Number(v).toLocaleString('es-MX') } },
            x: { grid: { display: false } }
        }
    }
});

const redNames = @json($misRegistrosPorRed->pluck('nombre'));
const redCounts = @json($misRegistrosPorRed->pluck('registros_count'));
const redColors = @json($misRegistrosPorRed->pluck('color'));

new Chart(document.getElementById('chartMisRedes'), {
    type: 'pie',
    data: {
        labels: redNames,
        datasets: [{
            data: redCounts,
            backgroundColor: redColors.length ? redColors : ['#bc1888'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } } }
    }
});
</script>
@endsection