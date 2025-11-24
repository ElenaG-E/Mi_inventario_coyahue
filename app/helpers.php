<?php

use Carbon\Carbon;

if (!function_exists('formatearDuracionGarantia')) {
    function formatearDuracionGarantia(Carbon $fechaCompra, ?int $duracionMeses): string
    {
        $duracionMeses = $duracionMeses ?? 0;

        // Sin garantía definida
        if ($duracionMeses <= 0) {
            return 'Sin garantía';
        }

        $vence = $fechaCompra->copy()->addMonths($duracionMeses);
        $diff = now()->diff($vence);

        if ($diff->days === 0) {
            return 'Vence hoy (' . $vence->format('d/m/Y') . ')';
        }

        if ($diff->invert === 1) {
            return 'Vencida el ' . $vence->format('d/m/Y');
        }

        $partes = [];
        if ($diff->y > 0) $partes[] = $diff->y . ' años';
        if ($diff->m > 0) $partes[] = $diff->m . ' meses';
        if ($diff->d > 0) {
            $semanas = intdiv($diff->d, 7);
            $dias = $diff->d % 7;
            if ($semanas > 0) $partes[] = $semanas . ' semanas';
            if ($dias > 0) $partes[] = $dias . ' días';
        }

        $texto = implode(', ', $partes);

        return $vence->format('d/m/Y') . ' (' . $texto . ' restantes)';
    }
}