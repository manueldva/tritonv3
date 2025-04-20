<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'empresa_id', // <-- Añadido
        'is_admin',   // <-- Añadido
        'is_active',   // <-- Añadido
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean', // <-- Castear a booleano
            'is_active' => 'boolean', // <-- Castear a booleano
        ];
    }

    // Método para verificar si el usuario es admin (más explícito)
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    // Método para verificar si el usuario es admin (más explícito)
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function empresa() // <--- ESTE NOMBRE EXACTO
    {
        // Asegúrate de que \App\Models\Empresa::class sea la ruta correcta a tu modelo Empresa
        return $this->belongsTo(\App\Models\Empresa::class);
    }
}
