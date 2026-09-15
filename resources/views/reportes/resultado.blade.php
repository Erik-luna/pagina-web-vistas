@extends('layout')

@section('title', 'Resultado del Reporte')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-chart-line me-2 text-gradient"></i> Resultado del Reporte</h4>
        <small class="text-muted">
            Del <strong>{{ request('fecha_desde') }}</strong> al <strong>{{ request('fecha_hasta') }}</strong>
            @if(request('red_social_id')) · {{ \App\Models\RedSocial::find(request('red_social_id'))->nombre ?? '' }} @endif
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.exportar', 'pdf') }}" class="btn btn-outline-danger"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
        <a href="{{ route('reportes.exportar', 'excel') }}" class="btn btn-outline-success"><i class="fa-solid fa-file-excel me-1"></i> Excel</a>
        <a href="{{ route('reportes.index') }}" class="btn btn-light border"><i class="fa-solid fa-plus me-1"></i> Nuevo Reporte</a>
    </div>
</div>

@if($registros->isEmpty())
    <div class="card card-content">
        <div class="empty-state py-5">
            <i class="fa-solid fa-file-circle-question"></i>
            <h6 class="fw-bold mt-3">Sin resultados</h6>
            <p class="text-muted">No se encontraron registros con los filtros seleccionados. Intenta ampliar el rango de fechas.</p>
            <a href="{{ route('reportes.index') }}" class="btn btn-primary-soc">Volver a configurar</a>
        </div>
    </div>
@else

<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="stat-card bg-ig">
            <i class="fa-solid fa-clipboard-list stat-icon"></i>
            <div class="stat-value">{{ $resumen['total_registros'] }}</div>
            <div class="stat-label">Registros</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card bg-fb">
            <i class="fa-solid fa-eye stat-icon"></i>
            <div class="stat-value">{{ number_format($resumen['total_vistas']) }}</div>
            <div class="stat-label">Total Vistas</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card bg-heart">
            <i class="fa-solid fa-heart stat-icon"></i>
            <div class="stat-value">{{ number_format($resumen['total_likes']) }}</div>
            <div class="stat-label">Total Likes</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card bg-wa">
            <i class="fa-solid fa-comment-dots stat-icon"></i>
            <div class="stat-value">{{ number_format($resumen['total_comentarios']) }}</div>
            <div class="stat-label">Comentarios</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card bg-tiktok">
            <i class="fa-solid fa-share-nodes stat-icon"></i>
            <div class="stat-value">{{ number_format($resumen['total_compartidos']) }}</div>
            <div class="stat-label">Compartidos</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card bg-purple">
            <i class="fa-solid fa-chart-line stat-icon"></i>
            <div class="stat-value">{{ number_format($resumen['promedio_vistas']) }}</div>
            <div class="stat-label">Prom. Vistas</div>
        </div>
    </div>
</div>

@if($resumen['mejor_registro'])
<div class="card card-content mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border:none">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="auth-logo" style="width:56px;height:56px;border-radius:16px;background:linear-gradient(45deg,#f09433,#bc1888);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;margin:0;box-shadow:none">
            <i class="fa-solid fa-crown"></i>
        </div>
        <div class="flex-grow-1">
            <small class="text-uppercase fw-bold" style="color:#fbbf24;letter-spacing:1px;font-size:.7rem">Contenido destacado del periodo</small>
            <h5 class="fw-bold mb-1" style="color:#fff">{{ $resumen['mejor_registro']->titulo }}</h5>
            <small style="color:#a0a3bd">
                <i class="fa-brands fa-{{ $resumen['mejor_registro']->redSocial->icono }}"></i>
                {{ $resumen['mejor_registro']->redSocial->nombre }}
                · {{ $resumen['mejor_registro']->usuario->nombre }}
                · {{ $resumen['mejor_registro']->fecha_registro->format('d/m/Y') }}
            </small>
        </div>
        <div class="text-end">
            <div style="color:#fff;font-size:1.8rem;font-weight:800"><i class="fa-solid fa-eye"></i> {{ number_format($resumen['mejor_registro']->vistas) }}</div>
            <small style="color:#fbbf24"><i class="fa-solid fa-heart"></i> {{ number_format($resumen['mejor_registro']->likes) }} likes</small>
        </div>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-area me-2 text-gradient"></i> Vistas por Dia</div>
            <div class="card-body">
                @php
                    $dias = $resumen['por_dia']->keys();
                    $vistasDia = $resumen['por_dia']->map(fn($v) => $v['vistas'])->values();
                    $likesDia = $resumen['por_dia']->map(fn($v) => $v['likes'])->values();
                @endphp
                <canvas id="chartPorDia" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-pie me-2 text-gradient"></i> Distribucion por Red Social</div>
            <div class="card-body">
                @php
                    $redLabels = $resumen['por_red']->pluck('nombre');
                    $redData = $resumen['por_red']->pluck('vistas');
                    $redColors = $resumen['por_red']->pluck('color');
                @endphp
                <canvas id="chartPorRed" height="190"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-chart-column me-2 text-gradient"></i> Resumen por Red Social</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th class="ps-4">Red</th><th class="text-center">Registros</th><th class="text-center">Vistas</th><th class="text-center">Likes</th><th class="text-end pe-4">% Vistas</th></tr>
                        </thead>
                        <tbody>
                            @foreach($resumen['por_red'] as $red)
                                <tr>
                                    <td class="ps-4">
                                        <span class="social-badge" style="background:{{ $red['color'] }}">
                                            <i class="fa-brands fa-{{ \App\Models\RedSocial::where('nombre',$red['nombre'])->first()->icono ?? 'hashtag' }}"></i> {{ $red['nombre'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $red['total'] }}</td>
                                    <td class="text-center fw-bold">{{ number_format($red['vistas']) }}</td>
                                    <td class="text-center">{{ number_format($red['likes']) }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="progress" style="width:70px;height:6px">
                                                <div class="progress-bar" style="width:{{ $resumen['total_vistas'] > 0 ? ($red['vistas']/$resumen['total_vistas'])*100 : 0 }}%;background:{{ $red['color'] }}"></div>
                                            </div>
                                            <small class="fw-semibold">{{ $resumen['total_vistas'] > 0 ? round(($red['vistas']/$resumen['total_vistas'])*100,1) : 0 }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-content h-100">
            <div class="card-header"><i class="fa-solid fa-inbox me-2 text-gradient"></i> Registros por Tipo de Contenido</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th class="ps-4">Tipo</th><th class="text-center">Registros</th><th class="text-center">Vistas</th><th class="text-end pe-4">% Registros</th></tr>
                        </thead>
                        <tbody>
                            @php $totalTipos = $resumen['por_tipo']->sum(fn($t) => $t['total']); @endphp
                            @foreach($resumen['por_tipo'] as $tipo => $data)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-estado text-capitalize bg-light border">
                                            @if($tipo == 'video') <i class="fa-solid fa-video me-1"></i>
                                            @elseif($tipo == 'reel') <i class="fa-solid fa-clapperboard me-1"></i>
                                            @elseif($tipo == 'stories') <i class="fa-regular fa-circle-dot me-1"></i>
                                            @elseif($tipo == 'live') <i class="fa-solid fa-tower-broadcast me-1"></i>
                                            @elseif($tipo == 'podcast') <i class="fa-solid fa-podcast me-1"></i>
                                            @elseif($tipo == 'texto') <i class="fa-solid fa-file-lines me-1"></i>
                                            @else <i class="fa-solid fa-image me-1"></i>
                                            @endif
                                            {{ ucfirst($tipo) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $data['total'] }}</td>
                                    <td class="text-center fw-bold">{{ number_format($data['vistas']) }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <div class="progress" style="width:70px;height:6px">
                                                <div class="progress-bar bg-gradient" style="width:{{ $totalTipos > 0 ? ($data['total']/$totalTipos)*100 : 0 }}%;background:linear-gradient(45deg,#f09433,#bc1888)"></div>
                                            </div>
                                            <small class="fw-semibold">{{ $totalTipos > 0 ? round(($data['total']/$totalTipos)*100,1) : 0 }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('usuario_rol') === 'admin' && $resumen['por_usuario']->isNotEmpty())
    <div class="card card-content mb-4">
        <div class="card-header"><i class="fa-solid fa-users me-2 text-gradient"></i> Vistas por Usuario</div>
        <div class="card-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th class="ps-0">Usuario</th><th class="text-center">Registros</th><th class="text-center">Vistas</th><th class="text-end pe-0">% del total</th></tr>
                </thead>
                <tbody>
                    @foreach($resumen['por_usuario'] as $usr)
                        <tr>
                            <td class="ps-0 fw-semibold">{{ $usr['nombre'] }}</td>
                            <td class="text-center">{{ $usr['total'] }}</td>
                            <td class="text-center fw-bold">{{ number_format($usr['vistas']) }}</td>
                            <td class="text-end pe-0">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="progress" style="width:70px;height:6px">
                                        <div class="progress-bar" style="width:{{ $resumen['total_vistas'] > 0 ? ($usr['vistas']/$resumen['total_vistas'])*100 : 0 }}%;background:#6d28d9"></div>
                                    </div>
                                    <small class="fw-semibold">{{ $resumen['total_vistas'] > 0 ? round(($usr['vistas']/$resumen['total_vistas'])*100,1) : 0 }}%</small>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card card-content">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-table me-2 text-gradient"></i> Detalle de Registros ({{ $registros->count() }})</span>
        <span class="badge rounded-pill bg-light text-dark border">{{ request('fecha_desde') }} - {{ request('fecha_hasta') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Titulo</th>
                        <th>Red</th>
                        <th>Usuario</th>
                        <th class="text-center">Vistas</th>
                        <th class="text-center">Likes</th>
                        <th class="text-center">Comentarios</th>
                        <th class="text-center">Compartidos</th>
                        <th class="text-end pe-4">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $registro)
                        <tr>
                            <td class="ps-4"><strong style="font-size:.88rem">{{ Str::limit($registro->titulo, 35) }}</strong></td>
                            <td>
                                <span class="social-badge" style="background:{{ $registro->redSocial->color }}">
                                    <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i> {{ $registro->redSocial->nombre }}
                                </span>
                            </td>
                            <td><small class="fw-semibold">{{ $registro->usuario->nombre }}</small></td>
                            <td class="text-center fw-bold" style="color:#dc2743">{{ number_format($registro->vistas) }}</td>
                            <td class="text-center" style="color:#1877F2">{{ number_format($registro->likes) }}</td>
                            <td class="text-center" style="color:#25D366">{{ number_format($registro->comentarios) }}</td>
                            <td class="text-center" style="color:#d97706">{{ number_format($registro->compartidos) }}</td>
                            <td class="text-end pe-4"><small>{{ $registro->fecha_registro->format('d/m/Y') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif
@endsection

@section('scripts')
<script>
@if($registros->isNotEmpty())
new Chart(document.getElementById('chartPorDia'), {
    type: 'line',
    data: {
        labels: @json($dias),
        datasets: [
            {
                label: 'Vistas',
                data: @json($vistasDia),
                borderColor: '#dc2743',
                backgroundColor: 'rgba(220,39,67,.1)',
                fill: true,
                tension: .35,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#dc2743'
            },
            {
                label: 'Likes',
                data: @json($likesDia),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,.1)',
                fill: true,
                tension: .35,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#3b82f6'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true } },
            tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${Number(c.raw).toLocaleString('es-MX')}` } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: (v) => Number(v).toLocaleString('es-MX') } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('chartPorRed'), {
    type: 'doughnut',
    data: {
        labels: @json($redLabels),
        datasets: [{
            data: @json($redData),
            backgroundColor: @json($redColors),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        cutout: '55%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } },
            tooltip: { callbacks: { label: (c) => ` ${c.label}: ${Number(c.raw).toLocaleString('es-MX')} vistas` } }
        }
    }
});
@endif
</script>
@endsection