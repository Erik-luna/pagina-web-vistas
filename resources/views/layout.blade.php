@php
    $notifCount = \App\Models\Notificacion::where('usuario_id', session('usuario_id'))->where('leida', false)->count();
    $notifs = \App\Models\Notificacion::where('usuario_id', session('usuario_id'))->where('leida', false)->latest()->take(5)->get();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SocialMetrics') - Panel de Vistas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --ig-gradient: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            --fb-color: #1877F2;
            --tw-color: #000000;
            --tw-x: #000000;
            --wa-color: #25D366;
            --yt-color: #FF0000;
            --tt-color: #000000;
            --in-color: #0A66C2;
            --pt-color: #E60023;
            --tg-color: #0088cc;
            --sp-color: #1DB954;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f0f2f5;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 1.5rem 1rem;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 3px;
        }

        .sidebar-logo {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .sidebar-logo h3 {
            font-weight: 800;
            background: var(--ig-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .sidebar-logo .icon-logo {
            font-size: 2.2rem;
            background: var(--ig-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .9rem;
            color: #a0a3bd;
            text-decoration: none;
            padding: .8rem 1rem;
            border-radius: 12px;
            margin-bottom: .3rem;
            font-size: .92rem;
            font-weight: 500;
            transition: all .25s ease;
        }

        .sidebar-link i {
            width: 22px;
            font-size: 1.1rem;
            text-align: center;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .sidebar-link.active {
            background: var(--ig-gradient);
            box-shadow: 0 4px 20px rgba(220,39,67,.4);
        }

        .sidebar-footer {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        .main-content {
            margin-left: 260px;
            padding: 1.5rem 2rem;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
            background: #fff;
            border-radius: 16px;
            padding: .9rem 1.5rem;
            box-shadow: 0 2px 20px rgba(0,0,0,.05);
        }

        .avatar-profile {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--ig-gradient);
            padding: 2px;
        }

        .notif-dropdown {
            max-height: 350px;
            overflow-y: auto;
            width: 340px;
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,.15);
        }

        .card-content {
            background: #fff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 20px rgba(0,0,0,.05);
        }

        .card-content .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            font-weight: 700;
            padding: 1.1rem 1.5rem;
        }

        .stat-card {
            border-radius: 16px;
            padding: 1.4rem;
            color: #fff;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            transition: transform .3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-icon {
            position: absolute;
            right: -10px;
            top: -10px;
            font-size: 5.5rem;
            opacity: .2;
        }

        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .stat-card .stat-label {
            font-size: .85rem;
            opacity: .9;
            font-weight: 500;
        }

        .bg-ig {
            background: linear-gradient(135deg, #f09433, #bc1888);
        }

        .bg-fb {
            background: linear-gradient(135deg, #1877F2, #0a4ea6);
        }

        .bg-tiktok {
            background: linear-gradient(135deg, #25F4EE, #000000 50%, #FE2C55);
        }

        .bg-yt {
            background: linear-gradient(135deg, #FF0000, #cc0000);
        }

        .bg-tw {
            background: linear-gradient(135deg, #000000, #333333);
        }

        .bg-wa {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .bg-heart {
            background: linear-gradient(135deg, #f5576c, #ff6b6b);
        }

        .bg-purple {
            background: linear-gradient(135deg, #a78bfa, #6d28d9);
        }

        .bg-teal {
            background: linear-gradient(135deg, #2dd4bf, #0f766e);
        }

        .bg-amber {
            background: linear-gradient(135deg, #fbbf24, #d97706);
        }

        .bg-indigo {
            background: linear-gradient(135deg, #6366f1, #4338ca);
        }

        .social-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .7rem;
            border-radius: 50px;
            font-size: .78rem;
            font-weight: 600;
            color: #fff;
        }

        .table thead th {
            border-bottom: 2px solid #eee;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6c757d;
        }

        .table td {
            vertical-align: middle;
            font-size: .9rem;
        }

        .btn-primary-soc {
            background: var(--ig-gradient);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: .55rem 1.3rem;
            transition: all .3s ease;
        }

        .btn-primary-soc:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220,39,67,.4);
            color: #fff;
        }

        .btn-outline-soc {
            border: 2px solid #bc1888;
            color: #bc1888;
            font-weight: 600;
            border-radius: 10px;
            padding: .45rem 1.2rem;
            transition: all .3s ease;
        }

        .btn-outline-soc:hover {
            background: #bc1888;
            color: #fff;
        }

        .text-gradient {
            background: var(--ig-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .badge-estado {
            padding: .4rem .8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: .75rem;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: .65rem 1rem;
            border: 1.5px solid #e2e8f0;
        }

        .form-control:focus, .form-select:focus {
            border-color: #bc1888;
            box-shadow: 0 0 0 .2rem rgba(220,39,67,.15);
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            position: relative;
            overflow: hidden;
        }

        .auth-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .5;
        }

        .auth-card {
            background: rgba(255,255,255,.98);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0,0,0,.5);
            width: 100%;
            max-width: 460px;
            padding: 2.5rem;
            z-index: 10;
            position: relative;
        }

        .auth-logo {
            width: 84px;
            height: 84px;
            border-radius: 22px;
            background: var(--ig-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: #fff;
            margin: 0 auto 1.2rem;
            box-shadow: 0 8px 30px rgba(220,39,67,.4);
        }

        .social-circles {
            display: flex;
            justify-content: center;
            gap: .8rem;
            margin: 1.3rem 0 1rem;
        }

        .social-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            transition: transform .3s;
            text-decoration: none;
        }

        .social-circle:hover {
            transform: scale(1.15);
        }

        .content-type-icon {
            font-size: 1.6rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: .3;
            margin-bottom: 1rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .3s ease;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 999;
                display: none;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }

        .pagination .page-link {
            border-radius: 8px;
            margin: 0 3px;
            color: #bc1888;
        }

        .pagination .page-item.active .page-link {
            background: var(--ig-gradient);
            border-color: transparent;
            color: #fff;
        }
    </style>
</head>
<body>
    @yield('auth-content')

    @if(session()->has('usuario_id'))
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

        <nav class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <i class="fa-brands fa-hashtag icon-logo"></i>
                <h3>SocialMetrics</h3>
                <small style="color:#a0a3bd;font-size:.72rem">Analitica de Vistas</small>
            </div>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="{{ route('registros.index') }}" class="sidebar-link {{ request()->routeIs('registros.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-list"></i> Registros de Vistas
            </a>
            <a href="{{ route('reportes.index') }}" class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-chart-line"></i> Reportes y Graficos
            </a>
            <a href="{{ route('notificaciones.index') }}" class="sidebar-link {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i> Notificaciones
                @if($notifCount > 0)
                    <span class="badge rounded-pill bg-danger ms-auto">{{ $notifCount }}</span>
                @endif
            </a>

            @if(session('usuario_rol') === 'admin')
                <div class="mt-4 mb-2" style="font-size:.72rem;color:#a0a3bd;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:0 1rem">
                    Administracion
                </div>
                <a href="{{ route('usuarios.index') }}" class="sidebar-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Gestionar Usuarios
                </a>
                <a href="{{ route('redes.index') }}" class="sidebar-link {{ request()->routeIs('redes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-share-nodes"></i> Redes Sociales
                </a>
            @endif

            <div class="sidebar-footer">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ session('usuario_avatar', 'https://ui-avatars.com/api/?name=U') }}" alt="avatar" class="avatar-profile" style="width:36px;height:36px">
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#fff">{{ session('usuario_nombre') }}</div>
                            <small style="color:#a0a3bd;font-size:.72rem;text-transform:capitalize">{{ session('usuario_rol') }}</small>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 text-danger" title="Cerrar sesion">
                            <i class="fa-solid fa-right-from-bracket fa-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="main-content">
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light border d-lg-none" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $titulo ?? 'Panel Principal' }}</h5>
                        <small class="text-muted">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-light border position-relative" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-bell"></i>
                            @if($notifCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                                    {{ $notifCount }}
                                </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                                <strong>Notificaciones</strong>
                                <form action="{{ route('notificaciones.todasLeer') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm text-decoration-none" style="color:#bc1888">Marcar todas</button>
                                </form>
                            </div>
                            @forelse($notifs as $notif)
                                <a href="{{ $notif->url ?? '#' }}" class="dropdown-item py-2 px-3 {{ $notif->leida ? '' : 'bg-light' }}">
                                    <i class="fa-solid fa-circle me-2" style="color:{{ $notif->leida ? '#ccc' : '#bc1888' }};font-size:.6rem"></i>
                                    <strong class="d-block" style="font-size:.85rem">{{ $notif->titulo }}</strong>
                                    <small class="text-muted">{{ Str::limit($notif->mensaje, 60) }}</small>
                                </a>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fa-regular fa-bell-slash fa-2x mb-2 d-block"></i>
                                    Sin notificaciones
                                </div>
                            @endforelse
                            <div class="p-2 border-top text-center">
                                <a href="{{ route('notificaciones.index') }}" class="btn btn-sm w-100 btn-primary-soc">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light border d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                            <img src="{{ session('usuario_avatar', 'https://ui-avatars.com/api/?name=U') }}" alt="avatar" class="avatar-profile" style="width:34px;height:34px">
                            <span class="fw-semibold d-none d-md-inline">{{ session('usuario_nombre') }}</span>
                            <i class="fa-solid fa-chevron-down small"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" style="border-radius:14px;border:none">
                            <div class="px-4 py-3 border-bottom">
                                <strong class="d-block">{{ session('usuario_nombre') }}</strong>
                                <small class="text-muted d-block">{{ session('usuario_email') ?? '' }}</small>
                                <span class="badge rounded-pill {{ session('usuario_rol') === 'admin' ? 'bg-danger' : 'bg-primary' }}" style="text-transform:capitalize">{{ session('usuario_rol') }}</span>
                            </div>
                            <a class="dropdown-item py-2" href="{{ route('dashboard') }}">
                                <i class="fa-solid fa-chart-line me-2" style="color:#bc1888"></i> Mi Dashboard
                            </a>
                            <a class="dropdown-item py-2" href="{{ route('registros.create') }}">
                                <i class="fa-solid fa-plus me-2" style="color:#1877F2"></i> Nuevo Registro
                            </a>
                            <hr class="dropdown-divider">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm rounded-3" style="border:none">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm rounded-3" style="border:none">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info d-flex align-items-center gap-2 shadow-sm rounded-3" style="border:none">
                    <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <script>
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('open');
                document.getElementById('sidebarBackdrop').classList.toggle('show');
            }
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>