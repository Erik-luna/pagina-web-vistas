<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    const UPDATED_AT = null;

    protected $fillable = ['usuario_id', 'titulo', 'mensaje', 'tipo', 'leida', 'url'];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
