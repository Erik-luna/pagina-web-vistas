@extends('layout')

@section('title', 'Notificaciones')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2 text-gradient"></i> Notificaciones</h4>
        <small class="text-muted">Centro de notificaciones y avisos del sistema</small>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('notificaciones.todasLeer') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-soc">
                <i class="fa-solid fa-check-double me-1"></i> Marcar todas como leidas
            </button>
        </form>
    </div>
</div>

<div class="card card-content">
    <div class="card-body p-2">
        @forelse($notificaciones as $notif)
            <div class="d-flex align-items-start gap-3 p-3 {{ $loop->first ? '' : 'border-top' }} {{ $notif->leida ? 'opacity-75' : 'bg-light-subtle' }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;{{ $notif->tipo == 'exito' ? 'background:#d1fae5;color:#059669' : ($notif->tipo == 'alerta' ? 'background:#fef3c7;color:#d97706' : ($notif->tipo == 'error' ? 'background:#fee2e2;color:#dc2626' : 'background:#dbeafe;color:#2563eb')) }}">
                    <i class="fa-solid fa-{{ $notif->tipo == 'exito' ? 'check' : ($notif->tipo == 'alerta' ? 'triangle-exclamation' : ($notif->tipo == 'error' ? 'xmark' : 'info')) }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong class="d-block" style="font-size:.92rem">{{ $notif->titulo }}</strong>
                        <small class="text-muted flex-shrink-0 ms-2">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2 text-muted" style="font-size:.88rem">{{ $notif->mensaje }}</p>
                    @if(!$notif->leida)
                        <form action="{{ route('notificaciones.leer', $notif->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary-soc">
                                <i class="fa-regular fa-circle-check me-1"></i> Marcar como leida
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-regular fa-bell-slash"></i>
                <h6 class="fw-bold mt-3">No tienes notificaciones</h6>
                <p class="text-muted">Cuando ocurran eventos importantes, los veras aqui.</p>
            </div>
        @endforelse
    </div>
    @if($notificaciones->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $notificaciones->links() }}
        </div>
    @endif
</div>
@endsection