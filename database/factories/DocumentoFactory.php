<?php

namespace Database\Factories;

use App\Models\Documento;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['factura', 'garantia', 'otro']);
        $extension = $this->faker->randomElement(['pdf', 'jpg', 'png']);
        
        return [
            'nombre_archivo' => $tipo . '_' . $this->faker->unique()->randomNumber(4) . '.' . $extension,
            'ruta_archivo' => 'storage/docs/' . $this->faker->uuid() . '.' . $extension,
            'tipo' => $tipo, // 'factura', 'garantia', 'otro'
            'fecha_subida' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tiempo_garantia_meses' => ($tipo === 'garantia') ? $this->faker->numberBetween(6, 36) : null,
        ];
    }
}
