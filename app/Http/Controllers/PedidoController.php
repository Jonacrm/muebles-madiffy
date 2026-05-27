<?php

namespace App\Http\Controllers;

use App\Services\CotizacionTotals;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function __construct(private readonly CotizacionTotals $totals) {}

    public function index(): View
    {
        return view('pedidos.index', [
            'pedidos' => [$this->pedido('2'), $this->pedido('3')],
        ]);
    }

    public function show(string $pedido): View
    {
        return view('pedidos.show', [
            'pedido' => $this->pedido($pedido),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pedido(string $id): array
    {
        $numero = max((int) $id, 1);
        $lineas = [
            [
                'sku' => 'SKU-001',
                'producto' => 'Mesa de comedor',
                'descripcion' => 'Mesa rectangular de madera para comedor',
                'cantidad' => 2,
                'precio_unitario' => 4500.00,
                'descuento_linea' => 500.00,
            ],
            [
                'sku' => 'SKU-002',
                'producto' => 'Silla tapizada',
                'descripcion' => 'Silla individual tapizada color arena',
                'cantidad' => 6,
                'precio_unitario' => 950.00,
                'descuento_linea' => 300.00,
            ],
        ];

        return array_merge([
            'id' => $numero,
            'folio' => sprintf('PED-2026-%03d', $numero),
            'cotizacion_id' => $numero,
            'cotizacion_folio' => sprintf('COT-2026-%03d', $numero),
            'cliente' => $numero === 3 ? 'Arquitectura Roble' : 'Hoteles del Norte',
            'fecha_pedido' => '2026-05-27',
            'estado' => 'Generado',
            'snapshot' => 'Este pedido copia los conceptos y precios pactados de la cotización; no depende del precio actual del catálogo.',
        ], $this->totals->calcular($lineas, 800.00));
    }
}
