<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 20 proveedores de prueba con datos completos (incluyendo RUT y dirección)
        Proveedor::factory(20)->create();
    }
}
