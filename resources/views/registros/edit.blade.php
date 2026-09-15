@extends('layout')

@section('title', 'Editar Registro')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('registros.index') }}" class="btn btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-pen me-2 text-gradient"></i> Editar Registro #{{ $registro->id }}</h4>
                <small class="text-muted">Actualiza las metricas del contenido</small>
            </div>
        </div>

        <div class="card card-content">
            <div class="card-body p-4">
                <div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
                    <span class="social-badge" style="background:{{ $registro->redSocial->color }}">
                        <i class="fa-brands fa-{{ $registro->redSocial->icono }}"></i> {{ $registro->redSocial->nombre }}
                    </span>
                    <div>
                        <strong>{{ $registro->titulo }}</strong>
                        <small class="d-block text-muted">Creado por {{ $registro->usuario->nombre }} el {{ $registro->created_at->format('d/m/Y') }}</small>
                    </div>
                    <span class="badge-estado ms-auto {{ $registro->estado == 'activo' ? 'bg-success-subtle text-success' : ($registro->estado == 'inactivo' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
                        {{ ucfirst($registro->estado) }}
                    </span>
                </div>

                <form action="{{ route('registros.update', $registro->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-share-nodes me-1" style="color:#bc1888"></i> Red Social <span class="text-danger">*</span></label>
                            <select name="red_social_id" class="form-select @error('red_social_id') is-invalid @enderror" required>
                                @foreach($redesSociales as $red)
                                    <option value="{{ $red->id }}" {{ old('red_social_id', $registro->red_social_id) == $red->id ? 'selected' : '' }}>
                                        {{ $red->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('red_social_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-tag me-1" style="color:#bc1888"></i> Tipo de Contenido <span class="text-danger">*</span></label>
                            <select name="tipo_contenido" class="form-select @error('tipo_contenido') is-invalid @enderror" required>
                                @foreach(['imagen','video','texto','stories','reel','live','podcast'] as $tipo)
                                    <option value="{{ $tipo }}" {{ old('tipo_contenido', $registro->tipo_contenido) == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                                @endforeach
                            </select>
                            @error('tipo_contenido') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-heading me-1" style="color:#bc1888"></i> Titulo del Contenido <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" value="{{ old('titulo', $registro->titulo) }}" maxlength="200"
                                class="form-control @error('titulo') is-invalid @enderror" required>
                            @error('titulo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-align-left me-1" style="color:#bc1888"></i> Descripcion</label>
                            <textarea name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $registro->descripcion) }}</textarea>
                            @error('descripcion') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-link me-1" style="color:#bc1888"></i> URL del Contenido</label>
                            <input type="url" name="url_contenido" value="{{ old('url_contenido', $registro->url_contenido) }}" maxlength="500"
                                class="form-control @error('url_contenido') is-invalid @enderror" placeholder="https://...">
                            @error('url_contenido') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-calendar-day me-1" style="color:#bc1888"></i> Fecha de Registro <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_registro" value="{{ old('fecha_registro', $registro->fecha_registro->format('Y-m-d')) }}"
                                class="form-control @error('fecha_registro') is-invalid @enderror" required>
                            @error('fecha_registro') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12">
                            <label class="form-label fw-bold"><i class="fa-solid fa-chart-column me-1 text-gradient"></i> Metricas del Contenido</label>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-eye me-1 text-danger"></i> Vistas <span class="text-danger">*</span></label>
                            <input type="number" name="vistas" value="{{ old('vistas', $registro->vistas) }}" min="0"
                                class="form-control @error('vistas') is-invalid @enderror" required>
                            @error('vistas') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-heart me-1 text-primary"></i> Likes <span class="text-danger">*</span></label>
                            <input type="number" name="likes" value="{{ old('likes', $registro->likes) }}" min="0"
                                class="form-control @error('likes') is-invalid @enderror" required>
                            @error('likes') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-comment-dots me-1 text-success"></i> Comentarios <span class="text-danger">*</span></label>
                            <input type="number" name="comentarios" value="{{ old('comentarios', $registro->comentarios) }}" min="0"
                                class="form-control @error('comentarios') is-invalid @enderror" required>
                            @error('comentarios') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-share-nodes me-1 text-warning"></i> Compartidos <span class="text-danger">*</span></label>
                            <input type="number" name="compartidos" value="{{ old('compartidos', $registro->compartidos) }}" min="0"
                                class="form-control @error('compartidos') is-invalid @enderror" required>
                            @error('compartidos') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-circle-info me-1" style="color:#bc1888"></i> Estado <span class="text-danger">*</span></label>
                            <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="activo" {{ old('estado', $registro->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $registro->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="borrador" {{ old('estado', $registro->estado) == 'borrador' ? 'selected' : '' }}>Borrador</option>
                            </select>
                            @error('estado') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('registros.index') }}" class="btn btn-light border px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary-soc px-5">
                                <i class="fa-solid fa-save me-1"></i> Actualizar Registro
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection