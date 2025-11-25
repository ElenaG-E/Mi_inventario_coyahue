<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosEquipoSeeder extends Seeder
{
    public function run(): void
    {
        // TRUNCATE es necesario si no se elimina la tabla o si hay fallos previos.
        DB::table('estados_equipo')->truncate(); 

        DB::table('estados_equipo')->insert([
            ['id' => 1, 'nombre' => 'Disponible'],
            ['id' => 2, 'nombre' => 'Asignado'],
            ['id' => 3, 'nombre' => 'Mantención'],
            ['id' => 4, 'nombre' => 'Baja'],
        ]);
    }
}
