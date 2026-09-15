<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedSocial extends Model
{
    protected $table = 'redes_sociales';
    const UPDATED_AT = null;

    protected $fillable = ['nombre', 'icono', 'color', 'url_logo', 'activa'];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroVista::class, 'red_social_id');
    }
}
