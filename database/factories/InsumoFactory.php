<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\EstadoEquipo;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsumoFactory extends Factory
{
    protected $model = Insumo::class;

    public function definition(): array
    {
        // Obtención segura de IDs
        $proveedor = Proveedor::inRandomOrder()->first();
        $sucursal = Sucursal::inRandomOrder()->first();
        $estado = EstadoEquipo::inRandomOrder()->first();

        // Contingencia: Si la siembra falló, creamos uno al vuelo para evitar el error 1452.
        // Esto asume que los modelos Proveedor, Sucursal, y EstadoEquipo tienen Factories.
        if (!$proveedor) { $proveedor = Proveedor::factory()->create(); }
        if (!$sucursal) { $sucursal = Sucursal::factory()->create(); }
        if (!$estado) { $estado = EstadoEquipo::factory()->create(); }

        return [
            'nombre' => $this->faker->randomElement(['Cartucho de Tinta', 'Mouse Inalámbrico', 'Teclado USB', 'Cable HDMI', 'Adaptador USB-C']),
            'cantidad' => $this->faker->numberBetween(10, 100),
            'estado_equipo_id' => $estado->id,
            'proveedor_id' => $proveedor->id,
            'sucursal_id' => $sucursal->id,
            'precio' => $this->faker->randomFloat(2, 5, 50),
            'fecha_compra' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'fecha_registro' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
