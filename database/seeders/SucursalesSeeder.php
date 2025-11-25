<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalesSeeder extends Seeder
{
    /**
     * Rellena la tabla 'sucursales' con datos de prueba.
     */
    public function run(): void
    {
        // Usamos DB::table()->insert para inserción masiva y eficiente
        DB::table('sucursales')->insert([
            [
                'id' => 1,
                'nombre' => 'Casa Matriz Santiago',
                'direccion' => 'Av. Apoquindo 4775, Las Condes, Santiago',
            ],
            [
                'id' => 2,
                'nombre' => 'Centro de Distribución (CD)',
                'direccion' => 'Ruta 68 Km 15, Pudahuel, Santiago',
            ],
            [
                'id' => 3,
                'nombre' => 'Sucursal Concepción',
                'direccion' => 'Calle Barros Arana 1000, Concepción',
            ],
            [
                'id' => 4,
                'nombre' => 'Oficina Regional Temuco',
                'direccion' => 'Manuel Montt 870, Temuco',
            ],
            [
                'id' => 5,
                'nombre' => 'Almacén Valparaíso',
                'direccion' => 'Avenida Errázuriz 1260, Valparaíso',
            ],
        ]);
    }
}
