<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Equipo;
use App\Models\Insumo;
use App\Models\Asignacion;
use App\Models\AsignacionInsumo;
use App\Models\TipoEquipo;
use App\Models\EstadoEquipo;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Rol;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener IDs básicos (asumiendo que los Seeders base ya se ejecutaron)
        $roles = Rol::pluck('id')->all();
        $estados = EstadoEquipo::pluck('id')->all();
        $tipos = TipoEquipo::pluck('id')->all();
        $proveedores = Proveedor::pluck('id')->all();
        $sucursales = Sucursal::pluck('id')->all();
        $estadoDisponibleId = EstadoEquipo::where('nombre', 'Disponible')->value('id');
        $estadoMantencionId = EstadoEquipo::where('nombre', 'Mantención')->value('id');
        
        // 2. Crear usuarios de prueba (10 usuarios)
        // Se crean 10 usuarios, uno de los cuales es el Admin (ID 1, creado en UserSeeder)
        $usuarios = Usuario::factory(9)->create();
        $admin = Usuario::first();
        if ($admin) {
            $usuarios->prepend($admin);
        }
        $usuarioIds = $usuarios->pluck('id')->all();

        $this->command->info("Creando 10 usuarios de prueba (incluido Admin)...");
        
        // 3. Crear 150 Equipos (con asignaciones y estados variados)
        $this->command->info("Creando 150 equipos de prueba...");
        Equipo::factory(150)->create([
            'estado_equipo_id' => function () use ($estados, $estadoMantencionId) {
                // 15% en Mantención, el resto distribuido en otros estados
                return (rand(1, 100) <= 15) ? $estadoMantencionId : $estados[array_rand($estados)];
            },
            'tipo_equipo_id' => $tipos[array_rand($tipos)],
            'proveedor_id' => $proveedores[array_rand($proveedores)],
            'sucursal_id' => $sucursales[array_rand($sucursales)],
        ])->each(function (Equipo $equipo) use ($usuarioIds, $estadoMantencionId) {
            // Asignar el equipo a un usuario (70% de probabilidad)
            if (rand(1, 100) <= 70 && $equipo->estado_equipo_id !== $estadoMantencionId) {
                $usuarioId = $usuarioIds[array_rand($usuarioIds)];

                Asignacion::create([
                    'equipo_id' => $equipo->id,
                    'usuario_id' => $usuarioId,
                    'fecha_asignacion' => now()->subDays(rand(10, 365)),
                    'motivo' => 'Asignación Inicial de Prueba',
                    'fecha_fin' => null, // Asignación activa
                ]);
            }
        });

        // 4. Crear 50 Insumos (con asignaciones y estados variados)
        $this->command->info("Creando 50 insumos de prueba...");
        Insumo::factory(50)->create([
            'estado_equipo_id' => $estados[array_rand($estados)],
            'proveedor_id' => $proveedores[array_rand($proveedores)],
            'sucursal_id' => $sucursales[array_rand($sucursales)],
        ])->each(function (Insumo $insumo) use ($usuarioIds) {
             // Asignar el insumo a un usuario (30% de probabilidad)
            if (rand(1, 100) <= 30) {
                $usuarioId = $usuarioIds[array_rand($usuarioIds)];
                
                AsignacionInsumo::create([
                    'insumo_id' => $insumo->id,
                    'usuario_id' => $usuarioId,
                    'cantidad' => rand(1, 5),
                    'fecha_asignacion' => now()->subDays(rand(10, 365)),
                    'motivo' => 'Asignación Inicial de Prueba',
                    'fecha_fin' => null, // Asignación activa
                ]);
            }
        });

        $this->command->info("¡Base de datos cargada con éxito con datos de demostración!");
    }
}
