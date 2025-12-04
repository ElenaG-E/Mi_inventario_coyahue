<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Equipo;
use App\Models\Insumo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 

// Importar todos los Seeders necesarios
use Database\Seeders\RolesSeeder;
use Database\Seeders\TiposEquipoSeeder;
use Database\Seeders\EstadosEquipoSeeder;
use Database\Seeders\ProveedoresSeeder;
use Database\Seeders\SucursalesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // ----------------------------------------------------
        // DESACTIVAR RESTRICCIONES DE CLAVE FORÁNEA (1)
        // ----------------------------------------------------
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // -------------------------------------------
        // 1. Datos estáticos con IDs fijos (Roles, Tipos, Estados)
        // -------------------------------------------
        $this->call(RolesSeeder::class); // Crea los roles
        $this->call(TiposEquipoSeeder::class);   
        $this->call(EstadosEquipoSeeder::class);  
        $this->call(ProveedoresSeeder::class); // Genera 20 proveedores
        $this->call(SucursalesSeeder::class);  // Crea 5 sucursales
        
        // -------------------------------------------
        // 2. Generación de Usuarios (Depende de Roles)
        // -------------------------------------------
        
        // Limpiar la tabla de Usuarios
        DB::table('usuarios')->truncate(); 
        
        // Admin de Prueba (rol_id 1)
        Usuario::create([
            'nombre' => 'Admin de Prueba',
            'email' => 'admin@coyahue.com',
            'password' => Hash::make('password'),
            'telefono' => '123456789',
            'rol_id' => 1,
            'estado' => 'activo',
        ]);

        // Usuario Administrador Adicional (rol_id 1)
        Usuario::create([
            'nombre' => 'Usuario Administrador',
            'email' => 'admin.coyahue.cl', 
            'password' => Hash::make('admin'),
            'telefono' => '',
            'rol_id' => 1,
            'estado' => 'activo',
        ]);
        
        // Generar 100 usuarios de prueba
        Usuario::factory(100)->create();

        // -------------------------------------------
        // REACTIVAR RESTRICCIONES DE CLAVE FORÁNEA (2)
        // -------------------------------------------
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // -------------------------------------------
        // 3. Generación de Equipos e Insumos (USAN FKs)
        // -------------------------------------------
        
        // Generar 40 equipos disponibles
        \App\Models\Equipo::factory(40)->create(); 

        // Generar 20 equipos asignados
        \App\Models\Equipo::factory(20)->asignado()->create(); 
        
        // Generar 50 insumos
        \App\Models\Insumo::factory(50)->create(); 
    }
}
