<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipo;

class RegenerarQREquipos extends Command
{
    protected $signature = 'equipos:regenerar-qr {--equipo_id= : ID del equipo específico}';
    protected $description = 'Regenera los códigos QR de todos los equipos con la URL correcta';

    public function handle()
    {
        $equipoId = $this->option('equipo_id');

        if ($equipoId) {
            // Regenerar QR de un solo equipo
            $equipos = Equipo::where('id', $equipoId)->get();
            if ($equipos->isEmpty()) {
                $this->error("❌ No se encontró el equipo con ID: {$equipoId}");
                return 1;
            }
            $this->info("🔄 Regenerando QR para el equipo ID: {$equipoId}...");
        } else {
            // Regenerar QR de todos los equipos
            $equipos = Equipo::all();
            $this->info("🔄 Regenerando QR para {$equipos->count()} equipo(s)...");
        }

        $bar = $this->output->createProgressBar($equipos->count());
        $bar->start();

        $actualizados = 0;
        $errores = 0;

        foreach ($equipos as $equipo) {
            try {
                // Generar la URL correcta
                $qrUrl = route('inventario.equipo', $equipo->id);
                
                // Actualizar el campo qr_code con la URL
                $equipo->update(['qr_code' => $qrUrl]);
                
                $actualizados++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error al actualizar equipo ID {$equipo->id}: " . $e->getMessage());
                $errores++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        // Resumen
        $this->info("✅ Proceso completado:");
        $this->line("   • Equipos actualizados: {$actualizados}");
        if ($errores > 0) {
            $this->warn("   ⚠ Errores: {$errores}");
        }

        return 0;
    }
}
