<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    const UPDATED_AT = null;

    protected $fillable = ['nombre', 'email', 'password', 'rol', 'avatar', 'telefono', 'activo'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroVista::class, 'usuario_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }
}
