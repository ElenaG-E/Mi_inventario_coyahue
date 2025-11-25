<?php

namespace Database\Factories;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\Proveedor;
use App\Models\EstadoEquipo;
use App\Models\Sucursal;
use App\Models\Usuario; // Necesario para el estado 'asignado'
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente.
     *
     * @var string
     */
    protected $model = Equipo::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // --- OBTENCIÓN SEGURA DE IDS DE CLAVE FORÁNEA ---
        // Utilizamos first()->id ?? 1. Si esto falla, intentamos una búsqueda más segura.
        
        $proveedor = Proveedor::inRandomOrder()->first();
        $sucursal = Sucursal::inRandomOrder()->first();
        
        // Si el seeder de Proveedores falló, creamos uno al vuelo (SOLUCIÓN DE CONTINGENCIA)
        if (!$proveedor) {
             $proveedor = Proveedor::factory()->create();
        }
        if (!$sucursal) {
             $sucursal = Sucursal::factory()->create();
        }
        
        $tipoEquipoId = TipoEquipo::inRandomOrder()->first()->id ?? 1;
        $estadoId = EstadoEquipo::where('nombre', 'Disponible')->first()->id ?? 1;


        // Generación de datos aleatorios para la tabla 'equipos'
        return [
            'tipo_equipo_id' => $tipoEquipoId,
            'proveedor_id' => $proveedor->id,
            'estado_equipo_id' => $estadoId,
            'sucursal_id' => $sucursal->id,

            'marca' => $this->faker->randomElement(['HP', 'Dell', 'Lenovo', 'Acer', 'Epson']),
            'modelo' => $this->faker->words(2, true),
            'numero_serie' => $this->faker->unique()->bothify('SN-????????####'),
            'precio' => $this->faker->randomFloat(2, 200, 2000),
            'fecha_compra' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'fecha_registro' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'qr_code' => $this->faker->unique()->randomNumber(5),
            'estado' => 'activo',
        ];
    }

    /**
     * Define un estado específico donde el equipo está asignado (usando IDs de usuario)
     */
    public function asignado()
    {
        return $this->state(function (array $attributes) {
            $usuarioId = Usuario::inRandomOrder()->first()->id ?? null; 
            
            return [
                'estado_equipo_id' => EstadoEquipo::where('nombre', 'Asignado')->first()->id ?? 2,
                'usuario_id' => $usuarioId,
            ];
        });
    }
}
