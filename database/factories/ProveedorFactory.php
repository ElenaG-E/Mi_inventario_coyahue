<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    /**
     * Helper para generar un RUT chileno simulado y válido con dígito verificador.
     * Utiliza el algoritmo módulo 11.
     */
    private function generarRutChileno(): string
    {
        // Genera el número base del RUT (sin dígito verificador)
        // Usamos between 7.000.000 y 99.999.999 para simular RUTs comunes de empresa
        $numeroBase = $this->faker->unique()->numberBetween(7000000, 99999999);
        
        $cuerpo = strval($numeroBase);
        $s = 1;
        for ($sum = 0, $i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $sum += $cuerpo[$i] * $s;
            $s = ($s == 7) ? 2 : $s + 1;
        }

        $resto = $sum % 11;
        $dv = 11 - $resto;

        $dv_char = match ($dv) {
            11 => '0',
            10 => 'K',
            default => strval($dv),
        };

        // Formato XX.XXX.XXX-X
        return number_format($numeroBase, 0, '', '.') . '-' . $dv_char;
    }


    public function definition(): array
    {
        return [
            // RUT generado con formato y DV válido
            'rut' => $this->generarRutChileno(),

            // Nombre de empresas relacionadas con TI
            'nombre' => $this->faker->randomElement([
                'Tech Global S.A.', 
                'Digital Supply Ltda.', 
                'Inversiones IT Solutions', 
                'Compu Mundo Chile',
                'Epson Chile Mayorista',
                'Acer Regional Tech',
                'Dell Partners',
            ]) . ' - ' . $this->faker->city(),
            
            // Campos de contacto (Asegurados como NOT NULL)
            'telefono' => $this->faker->numerify('+56 9 ########'),
            'correo' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->streetAddress() . ', ' . $this->faker->city(),
        ];
    }
}
