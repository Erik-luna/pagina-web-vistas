<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroVista extends Model
{
    protected $table = 'registros_vistas';
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id', 'red_social_id', 'titulo', 'descripcion',
        'vistas', 'likes', 'comentarios', 'compartidos',
        'estado', 'tipo_contenido', 'url_contenido', 'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'vistas' => 'integer',
            'likes' => 'integer',
            'comentarios' => 'integer',
            'compartidos' => 'integer',
            'fecha_registro' => 'date',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function redSocial(): BelongsTo
    {
        return $this->belongsTo(RedSocial::class, 'red_social_id');
    }
}
