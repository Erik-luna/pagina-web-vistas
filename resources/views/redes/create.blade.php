@extends('layout')

@section('title', 'Nueva Red Social')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('redes.index') }}" class="btn btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-share-nodes me-2 text-gradient"></i> Nueva Red Social</h4>
                <small class="text-muted">Registra una nueva plataforma en el sistema</small>
            </div>
        </div>

        <div class="card card-content">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="text-muted" style="font-size:3rem" id="iconoPreview">
                        <i class="fa-brands fa-hashtag"></i>
                    </div>
                    <small class="text-muted d-block">Vista previa del icono</small>
                </div>

                <form action="{{ route('redes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la red <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Instagram" required>
                        @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icono FontAwesome <span class="text-danger">*</span></label>
                            <input type="text" name="icono" value="{{ old('icono', 'instagram') }}" class="form-control @error('icono') is-invalid @enderror" placeholder="Ej. instagram, facebook, tiktok" required>
                            <small class="text-muted">Sin el prefijo "fa-brands fa-"</small>
                            @error('icono') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Color de marca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" id="colorPicker" value="{{ old('color', '#bc1888') }}" class="form-control form-control-color" style="width:48px">
                                <input type="text" name="color" id="colorTexto" value="{{ old('color', '#bc1888') }}" class="form-control" required>
                            </div>
                            @error('color') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">URL del logo</label>
                        <input type="url" name="url_logo" value="{{ old('url_logo') }}" class="form-control @error('url_logo') is-invalid @enderror" placeholder="https://...">
                        @error('url_logo') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-2 pt-3 border-top">
                        <a href="{{ route('redes.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary-soc px-5"><i class="fa-solid fa-check me-1"></i> Crear Red Social</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const iconoInput = document.querySelector('input[name="icono"]');
const preview = document.getElementById('iconoPreview');
iconoInput.addEventListener('input', function() {
    preview.innerHTML = `<i class="fa-brands fa-${this.value || 'hashtag'}"></i>`;
});

const colorPicker = document.getElementById('colorPicker');
const colorTexto = document.getElementById('colorTexto');
colorPicker.addEventListener('input', function() {
    colorTexto.value = this.value;
});
colorTexto.addEventListener('input', function() {
    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        colorPicker.value = this.value;
    }
});
</script>
@endsection