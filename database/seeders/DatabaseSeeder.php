<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Equipo;
use App\Models\Insumo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 

// Importar todos los Seeders necesarios
// Es importante que UserSeeder y DemoDataSeeder se llamen al final
// porque dependen de Roles, Estados, Tipos, Proveedores y Sucursales.
use Database\Seeders\RolesSeeder;
use Database\Seeders\TiposEquipoSeeder;
use Database\Seeders\EstadosEquipoSeeder;
use Database\Seeders\ProveedoresSeeder;
use Database\Seeders\SucursalesSeeder;
use Database\Seeders\DemoDataSeeder; 
use Database\Seeders\UsuarioSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ----------------------------------------------------
        // DESACTIVAR RESTRICCIONES DE CLAVE FORÁNEA
        // Esto es necesario para ejecutar TRUNCATE en tablas con FKs
        // ----------------------------------------------------
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // -------------------------------------------
        // 1. Llamada a todos los Seeders
        // -------------------------------------------
        $this->call([
            // Datos Estructurales (Base Data)
            RolesSeeder::class,           // 1. Roles
            EstadosEquipoSeeder::class,   // 2. Estados de Equipo
            TiposEquipoSeeder::class,     // 3. Tipos de Equipo
            SucursalesSeeder::class,      // 4. Sucursales
            ProveedoresSeeder::class,     // 5. Proveedores
            DemoDataSeeder::class,
	    UsuarioSeeder::class,        // 7. Crea equipos, insumos y asignaciones
        ]);

        // -------------------------------------------
        // REACTIVAR RESTRICCIONES DE CLAVE FORÁNEA
        // -------------------------------------------
        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); 
    }
}
