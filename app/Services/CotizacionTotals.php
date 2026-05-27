<?php

namespace App\Services;

class CotizacionTotals
{
    /**
     * @param  array<int, array<string, mixed>>  $lineas
     * @return array<string, mixed>
     */
    public function calcular(array $lineas, float $descuentoGlobal = 0, float $ivaRate = 0.16): array
    {
        $lineasCalculadas = array_map(function (array $linea): array {
            $cantidad = (float) $linea['cantidad'];
            $precioUnitario = (float) $linea['precio_unitario'];
            $descuentoLinea = (float) $linea['descuento_linea'];

            return array_merge($linea, [
                'subtotal' => round(($cantidad * $precioUnitario) - $descuentoLinea, 2),
            ]);
        }, $lineas);

        $subtotal = round(array_sum(array_column($lineasCalculadas, 'subtotal')), 2);
        $base = round(max($subtotal - $descuentoGlobal, 0), 2);
        $iva = round($base * $ivaRate, 2);

        return [
            'lineas' => $lineasCalculadas,
            'subtotal' => $subtotal,
            'descuento_global' => round($descuentoGlobal, 2),
            'base' => $base,
            'iva' => $iva,
            'total' => round($base + $iva, 2),
        ];
    }
}
