<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'rol_id',
        'estado',   // 👈 asegúrate de incluirlo si lo usas en la tabla
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // *******************************************
    // Implementación explícita del factory
    // NECESARIO para modelos con nombres custom (Usuario en lugar de User)
    // *******************************************
    protected static function newFactory()
    {
        return \Database\Factories\UsuarioFactory::new();
    }

    // Relación con rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación con asignaciones históricas
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'usuario_id');
    }

    // Relación con movimientos
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'usuario_id');
    }

    // Relación inversa: equipos asociados directamente
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'usuario_id');
    }

    // Relación inversa: insumos asociados directamente
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'usuario_id');
    }

    // Este método define qué campo se usa como "username" en el login
    public function username()
    {
        return 'email'; // asegura que coincida con tu columna real
    }
}
