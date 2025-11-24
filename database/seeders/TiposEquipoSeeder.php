<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Corregido: La clase debe ser PLURAL para coincidir con la llamada en DatabaseSeeder.php
class TiposEquipoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_equipo')->insert([
            ['nombre' => 'Notebook', 'categoria' => 'equipo'],
            ['nombre' => 'PC Escritorio', 'categoria' => 'equipo'],
            ['nombre' => 'Servidor', 'categoria' => 'equipo'],
            ['nombre' => 'Tablet', 'categoria' => 'equipo'],
            ['nombre' => 'Smartphone', 'categoria' => 'equipo'],
            ['nombre' => 'Monitor', 'categoria' => 'equipo'],
            ['nombre' => 'Proyector', 'categoria' => 'equipo'],
            ['nombre' => 'Impresora', 'categoria' => 'equipo'],
            ['nombre' => 'Router', 'categoria' => 'equipo'],
            ['nombre' => 'Switch', 'categoria' => 'equipo'],
            ['nombre' => 'Access Point', 'categoria' => 'equipo'],
            ['nombre' => 'Cámara Web', 'categoria' => 'equipo'],
        ]);
    }
}
