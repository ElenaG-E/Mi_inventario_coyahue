<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoEquipo; // <--- ¡ASEGURAR ESTA IMPORTACIÓN!

class TiposEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['categoria' => 'equipo', 'nombre' => 'Notebook'],
            ['categoria' => 'equipo', 'nombre' => 'PC Escritorio'],
            ['categoria' => 'equipo', 'nombre' => 'Servidor'],
            ['categoria' => 'equipo', 'nombre' => 'Tablet'],
            ['categoria' => 'equipo', 'nombre' => 'Smartphone'],
            ['categoria' => 'equipo', 'nombre' => 'Monitor'],
            ['categoria' => 'equipo', 'nombre' => 'Proyector'],
            ['categoria' => 'equipo', 'nombre' => 'Impresora'],
            ['categoria' => 'equipo', 'nombre' => 'Router'],
            ['categoria' => 'equipo', 'nombre' => 'Switch'],
            ['categoria' => 'equipo', 'nombre' => 'Access Point'],
            ['categoria' => 'equipo', 'nombre' => 'Cámara Web'],
        ];

        foreach ($tipos as $tipo) {
            // Usa TipoEquipo::firstOrCreate para evitar el error de ID duplicado (1062)
            TipoEquipo::firstOrCreate(
                ['nombre' => $tipo['nombre']], // Buscar por nombre (columna única)
                ['categoria' => $tipo['categoria']] // Si no existe, crear con estos datos
            );
        }
    }
}
