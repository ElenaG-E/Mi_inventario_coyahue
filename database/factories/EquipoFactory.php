<?php

namespace Database\Factories;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\Proveedor;
use App\Models\EstadoEquipo;
use App\Models\Sucursal;
use App\Models\Usuario; // Necesario para el estado 'asignado'
use App\Models\Asignacion; // Necesario para crear el registro de asignación
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente.
     */
    protected $model = Equipo::class;

    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        // --- OBTENCIÓN SEGURA DE IDs DE CLAVE FORÁNEA ---
        // Usamos first() para obtener el primer registro si existe, o creamos uno de contingencia.
        $proveedor = Proveedor::inRandomOrder()->first();
        $sucursal = Sucursal::inRandomOrder()->first();
        
        // Factories de contingencia: Si no hay proveedores/sucursales, creamos uno al vuelo.
        if (!$proveedor) { $proveedor = Proveedor::factory()->create(); }
        if (!$sucursal) { $sucursal = Sucursal::factory()->create(); }
        
        // Obtener IDs de tablas estáticas
        $tipoEquipoId = TipoEquipo::inRandomOrder()->first()->id ?? 1;
        $estadoId = EstadoEquipo::where('nombre', 'Disponible')->first()->id ?? 1;


        // Generación de datos aleatorios para la tabla 'equipos'
        return [
            'tipo_equipo_id' => $tipoEquipoId,
            'proveedor_id' => $proveedor->id,
            'estado_equipo_id' => $estadoId, // Por defecto, es 'Disponible'
            'sucursal_id' => $sucursal->id,

            'marca' => $this->faker->randomElement(['HP', 'Dell', 'Lenovo', 'Acer', 'Epson']),
            'modelo' => $this->faker->words(2, true),
            'numero_serie' => $this->faker->unique()->bothify('SN-????????####'),
            'precio' => $this->faker->randomFloat(2, 200, 2000),
            'fecha_compra' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'fecha_registro' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'qr_code' => $this->faker->unique()->randomNumber(5),
            'estado' => 'activo',
            // CRÍTICO: NO incluimos 'usuario_id' para evitar el error de columna, se usa 'afterCreating'.
        ];
    }

    /**
     * Define un estado específico donde el equipo está asignado.
     * Esta función usa afterCreating para insertar el registro en la tabla de pivote 'asignaciones'.
     */
    public function asignado()
    {
        return $this->afterCreating(function (Equipo $equipo) {
            $usuarioId = Usuario::inRandomOrder()->first()->id ?? null; 
            $asignadoId = EstadoEquipo::where('nombre', 'Asignado')->first()->id ?? 2;

            if ($usuarioId) {
                // 1. Crear el registro en la tabla de asignaciones
                \App\Models\Asignacion::create([ 
                    'equipo_id' => $equipo->id,
                    'usuario_id' => $usuarioId,
                    'fecha_asignacion' => now(),
                    'motivo' => 'Asignación generada por Factory',
                ]);
            }
            
            // 2. Actualizar el estado del equipo en la tabla principal
            $equipo->estado_equipo_id = $asignadoId;
            $equipo->save();

        })->state(function (array $attributes) {
            // Aseguramos que el estado inicial de la Factory sea "asignado"
            return [
                'estado_equipo_id' => EstadoEquipo::where('nombre', 'Asignado')->first()->id ?? 2,
            ];
        });
    }
}
