<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // -------------------------------------------
        // 1. Datos estáticos (Roles, Estados, Tipos)
        // -------------------------------------------
        $this->call(RolesSeeder::class);
        $this->call(TiposEquipoSeeder::class);    // <-- ¡Agregado!
        $this->call(EstadosEquipoSeeder::class);  // <-- ¡Agregado!
        
        // -------------------------------------------
        // 2. Usuarios base (Dependen de Roles)
        // -------------------------------------------
        
        // Admin de Prueba
        \App\Models\Usuario::create([
            'nombre' => 'Admin de Prueba',
            'email' => 'admin@coyahue.com',
            'password' => \Hash::make('password'),
            'telefono' => '123456789',
            'rol_id' => 1,
            'estado' => 'activo',
        ]);

        // Usuario Administrador Adicional
        \App\Models\Usuario::create([
            'nombre' => 'Usuario Administrador',
            // OJO: El email 'admin.coyahue.cl' parece incorrecto. 
            // Podrías querer usar algo como 'admin2@coyahue.cl'
            'email' => 'admin.coyahue.cl', 
            'password' => \Hash::make('admin'),
            'telefono' => '',
            'rol_id' => 1,
            'estado' => 'activo',
        ]);

        // -------------------------------------------
        // 3. Datos de prueba con Factory (Opcional)
        // -------------------------------------------
        // Aquí podrías llamar a factories si creaste más datos de prueba:
        // \App\Models\Usuario::factory(10)->create();
    }
}
