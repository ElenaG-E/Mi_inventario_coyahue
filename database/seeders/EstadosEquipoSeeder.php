<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// CORRECCIÓN: El nombre de la clase DEBE ser plural (EstadosEquipoSeeder) para coincidir con la llamada en DatabaseSeeder.php
class EstadosEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Los datos que ya definimos
        DB::table('estados_equipo')->insert([
            ['id' => 1, 'nombre' => 'Disponible'],
            ['id' => 2, 'nombre' => 'Asignado'],
            ['id' => 3, 'nombre' => 'Mantención'],
            ['id' => 4, 'nombre' => 'Baja'],
        ]);
    }
}
