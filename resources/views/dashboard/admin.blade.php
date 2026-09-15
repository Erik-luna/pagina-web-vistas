@extends('layout')

@section('title', 'Dashboard')

@section('content')
@php
    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $chartLabels = $vistasPorMes->map(fn($r) => $meses[$r->mes] . ' ' . $r->anio)->values();
    $chartVistas = $vistasPorMes->map(fn($r) => $r->total_vistas)->values();
    $chartLikes = $vistasPorMes->map(fn($r) => $r->total_likes)->values();
    $chartRegistros = $vistasPorMes->map(fn($r) => $r->total_registros)->values();
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-ig">
            <i class="fa-solid fa-eye stat-icon"></i>
            <div class="stat-value">{{ number_format($totalVistas) }}</div>
            <div class="stat-label"><i class="fa-solid fa-eye me-1"></i> Total Vistas</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-fb">
            <i class="fa-solid fa-heart stat-icon"></i>
            <div class="stat-value">{{ number_format($totalLikes) }}</div>
            <div class="stat-label"><i class="fa-solid fa-heart me-1"></i> Total Likes</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-wa">
            <i class="fa-solid fa-comment-dots stat-icon"></i>
            <div class="stat-value">{{ number_format($totalComentarios) }}</div>
            <div class="stat-label"><i class="fa-solid fa-comment-dots me-1"></i> Comentarios</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-tiktok">
            <i class="fa-solid fa-share-nodes stat-icon"></i>
            <div class="stat-value">{{ number_format($totalCompartidos) }}</div>
            <div class="stat-label"><i class="fa-solid fa-share-nodes me-1"></i> Compartidos</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-heart">
            <i class="fa-solid fa-clipboard-list stat-icon"></i>
            <div class="stat-value">{{ number_format($totalRegistros) }}</div>
            <div class="stat-label"><i class="fa-solid fa-clipboard-list me-1"></i> Registros</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-purple">
            <i class="fa-solid fa-users stat-icon"></i>
            <div class="stat-value">{{ number_format($totalUsuarios) }}</div>
            <div class="stat-label"><i class="fa-solid fa-users me-1"></i> Usuarios</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card bg-teal">
            <i class="fa-regular fa-calendar-check stat-icon"></i>
            <div class="stat-value">{{ $registrosHoy }}</div>
            <div class="stat-label"><i class="fa-regular fa-calendar-check me-1"></i> Registros Hoy</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-content h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-chart-area me-2 text-gradient"></i> Rendimiento Mensual</span>
                <span class="badge rounded-pill" style="background:#fee2e2;color:#dc2743">Ultimos 12 meses</span>
            </div>
            <div class="card-body">
                <canvas id="chartMensual" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-pie me-2 text-gradient"></i> Registros por Red Social</div>
            <div class="card-body">
                <canvas id="chartRedes" height="190"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-fire me-2 text-gradient"></i> Top 5 Contenidos Populares</div>
            <div class="card-body p-2">
                @forelse($topContenido as $index => $registro)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <span class="badge rounded-pill" style="background:{{ $registro->redSocial->color ?? '#bc1888' }};font-size:.9rem">{{ $index + 1 }}</span>
                        <div class="content-type-icon me-1">
                            @if($registro->tipo_contenido == 'video') <i class="fa-solid fa-video" style="color:#dc2743"></i>
                            @elseif($registro->tipo_contenido == 'reel') <i class="fa-solid fa-clapperboard" style="color:#bc1888"></i>
                            @elseif($registro->tipo_contenido == 'stories') <i class="fa-regular fa-circle-dot" style="color:#f09433"></i>
                            @elseif($registro->tipo_contenido == 'live') <i class="fa-solid fa-tower-broadcast" style="color:#dc2743"></i>
                            @elseif($registro->tipo_contenido == 'podcast') <i class="fa-solid fa-podcast" style="color:#6d28d9"></i>
                            @elseif($registro->tipo_contenido == 'texto') <i class="fa-solid fa-file-lines" style="color:#1877F2"></i>
                            @else <i class="fa-solid fa-image" style="color:#25D366"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <strong class="d-block" style="font-size:.9rem">{{ Str::limit($registro->titulo, 40) }}</strong>
                            <small class="text-muted">
                                <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i>
                                {{ $registro->redSocial->nombre }} · {{ $registro->fecha_registro->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <strong class="d-block" style="color:#dc2743;font-size:.95rem">
                                <i class="fa-solid fa-eye"></i> {{ number_format($registro->vistas) }}
                            </strong>
                            <small class="text-muted"><i class="fa-solid fa-heart" style="color:#dc2743"></i> {{ number_format($registro->likes) }}</small>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-fire-burner"></i>
                        <p class="text-muted">Aun no hay contenidos registrados</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-trophy me-2 text-gradient"></i> Top 5 Usuarios por Vistas</div>
            <div class="card-body p-2">
                @forelse($usuariosTop as $index => $usuario)
                    @php $sumVistas = $usuario->registros->sum('vistas'); @endphp
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <img src="{{ $usuario->avatar }}" alt="avatar" class="rounded-circle" style="width:42px;height:42px">
                        <div class="flex-grow-1">
                            <strong class="d-block" style="font-size:.9rem">{{ $usuario->nombre }}</strong>
                            <small class="text-muted">
                                <i class="fa-solid fa-clipboard-list"></i> {{ $usuario->registros_count }} registros
                                · <span class="text-capitalize">{{ $usuario->rol }}</span>
                            </small>
                        </div>
                        <div class="text-end">
                            <strong class="d-block {{ $index == 0 ? 'text-gradient' : '' }}" style="font-size:1.1rem">
                                {{ $index == 0 ? '<i class="fa-solid fa-crown"></i> ' : '' }}{!! $index == 0 ? '' : '' !!}{{ number_format($sumVistas) }}
                            </strong>
                            <small class="text-muted">vistas</small>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-users"></i>
                        <p class="text-muted">Sin datos de usuarios</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-arrow-trend-up me-2 text-gradient"></i> Rendimiento de Vistas por Red</div>
            <div class="card-body p-2">
                @foreach($registrosPorRed as $red)
                    @php $sumVistas = $red->registros->sum('vistas'); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="social-badge" style="background:{{ $red->color }}">
                                <i class="fa-brands fa-{{ $red->icono }}"></i> {{ $red->nombre }}
                            </span>
                            <small class="fw-semibold">{{ number_format($sumVistas) }} vistas</small>
                        </div>
                        <div class="progress" style="height:8px;overflow:hidden;border-radius:50px;background:#f1f5f9">
                            @php $maxVistas = max($registrosPorRed->map(fn($r) => $r->registros->sum('vistas'))->toArray() ?: [1]); @endphp
                            <div class="progress-bar" role="progressbar" style="width:{{ $maxVistas > 0 ? ($sumVistas / $maxVistas) * 100 : 0 }}%;background:{{ $red->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-content h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-clock-rotate-left me-2 text-gradient"></i> Actividad Reciente</span>
                <a href="{{ route('registros.index') }}" class="btn btn-sm btn-outline-soc">Ver todos</a>
            </div>
            <div class="card-body p-2" style="max-height:400px;overflow-y:auto">
                @forelse($registrosRecientes as $registro)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <span class="social-badge" style="background:{{ $registro->redSocial->color }}">
                            <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i>
                        </span>
                        <div class="flex-grow-1">
                            <strong class="d-block" style="font-size:.88rem">{{ Str::limit($registro->titulo, 45) }}</strong>
                            <small class="text-muted">
                                Por {{ $registro->usuario->nombre }} · {{ $registro->fecha_registro->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <strong style="color:#dc2743;font-size:.9rem"><i class="fa-solid fa-eye"></i> {{ number_format($registro->vistas) }}</strong>
                            <div>
                                <span class="badge-estado {{ $registro->estado == 'activo' ? 'bg-success-subtle text-success' : ($registro->estado == 'inactivo' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
                                    {{ ucfirst($registro->estado) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <p class="text-muted">No hay actividad reciente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const labels = @json($chartLabels);
const vistas = @json($chartVistas);
const likes = @json($chartLikes);
const registros = @json($chartRegistros);

new Chart(document.getElementById('chartMensual'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Vistas',
                data: vistas,
                borderColor: '#dc2743',
                backgroundColor: 'rgba(220,39,67,.1)',
                fill: true,
                tension: .4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#dc2743'
            },
            {
                label: 'Likes',
                data: likes,
                borderColor: '#1877F2',
                backgroundColor: 'rgba(24,119,242,.1)',
                fill: true,
                tension: .4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#1877F2'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } },
            tooltip: {
                callbacks: { label: (c) => ` ${c.dataset.label}: ${Number(c.raw).toLocaleString('es-MX')}` }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: (v) => Number(v).toLocaleString('es-MX') } },
            x: { grid: { display: false } }
        }
    }
});

const redNames = @json($registrosPorRed->pluck('nombre'));
const redCounts = @json($registrosPorRed->pluck('registros_count'));
const redColors = @json($registrosPorRed->pluck('color'));

new Chart(document.getElementById('chartRedes'), {
    type: 'doughnut',
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
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } }
        },
        cutout: '60%'
    }
});
</script>
@endsection