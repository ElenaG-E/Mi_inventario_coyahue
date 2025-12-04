<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el Usuario Administrador Principal (Rol ID 1)
        Usuario::create([
            'nombre' => 'Administrador Principal',
            'email' => 'admin@coyahue.com', // Credencial de acceso
            'password' => Hash::make('password'), // Contraseña: password
            'telefono' => '987654321',
            'rol_id' => 1, 
            'estado' => 'activo',
        ]);
        
        // 2. Crear 100 Usuarios Estándar usando la Factory (Rol ID 2)
        // Esto asume que tienes un UsuarioFactory.php configurado.
        Usuario::factory(100)->create([
            'rol_id' => 2,
            'estado' => 'activo',
        ]);
        
        $this->command->info('100 Usuarios Estándar (Rol ID 2) creados con éxito.');
    }
}
