<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente.
     *
     * @var string
     */
    protected $model = Usuario::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Se generan datos aleatorios para los campos
        return [
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // --- CAMPOS ELIMINADOS O AJUSTADOS ---
            // 'email_verified_at' => now(), // ¡Eliminado para evitar fallos si la columna no existe!
            'password' => Hash::make('password'),
            'telefono' => $this->faker->numerify('#########'),
            'rol_id' => $this->faker->numberBetween(1, 3), // Asume roles 1, 2, 3
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
            // 'remember_token' => Str::random(10), // ¡ELIMINADO! Laravel ya no lo genera por defecto si no está en la migración.
            // ------------------------------------
        ];
    }
}
