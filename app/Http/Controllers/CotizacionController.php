<?php

namespace App\Http\Controllers;

use App\Services\CotizacionTotals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function __construct(private readonly CotizacionTotals $totals) {}

    public function index(): View
    {
        return view('cotizaciones.index', [
            'cotizaciones' => $this->cotizaciones(),
        ]);
    }

    public function create(): View
    {
        return view('cotizaciones.create', [
            'cotizacion' => $this->cotizacion('1'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('cotizaciones.index')->with('status', 'Cotización guardada temporalmente.');
    }

    public function show(string $cotizacion): View
    {
        return view('cotizaciones.show', [
            'cotizacion' => $this->cotizacion($cotizacion),
        ]);
    }

    public function edit(string $cotizacion): View
    {
        return view('cotizaciones.edit', [
            'cotizacion' => $this->cotizacion($cotizacion),
        ]);
    }

    public function update(Request $request, string $cotizacion): RedirectResponse
    {
        return redirect()->route('cotizaciones.show', $cotizacion)->with('status', 'Cotización actualizada.');
    }

    public function destroy(string $cotizacion): RedirectResponse
    {
        return redirect()->route('cotizaciones.index')->with('status', 'Cotización eliminada.');
    }

    public function convertir(string $cotizacion): RedirectResponse
    {
        return redirect()
            ->route('pedidos.show', $cotizacion)
            ->with('status', 'Cotización convertida a pedido.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cotizaciones(): array
    {
        return [
            $this->cotizacion('1'),
            $this->cotizacion('2'),
            $this->cotizacion('3'),
            $this->cotizacion('4'),
            $this->cotizacion('5'),
            $this->cotizacion('6'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cotizacion(string $id): array
    {
        $estados = [
            '1' => 'Borrador',
            '2' => 'Aceptada',
            '3' => 'Convertida',
            '4' => 'Vencida',
            '5' => 'Rechazada',
            '6' => 'Enviada',
        ];

        $clientes = [
            '1' => 'Empresa Mueblera S.A.',
            '2' => 'Hoteles del Norte',
            '3' => 'Arquitectura Roble',
            '4' => 'Casa Modelo Hermosillo',
            '5' => 'Distribuidora Nova',
            '6' => 'Oficinas Sierra',
        ];

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

        $numero = max((int) $id, 1);

        return array_merge([
            'id' => $numero,
            'folio' => sprintf('COT-2026-%03d', $numero),
            'cliente' => $clientes[$id] ?? 'Cliente de demostración',
            'rfc' => 'XAXX010101000',
            'vendedor' => 'Alan Lovelace',
            'fecha_emision' => '2026-05-26',
            'vigencia' => '2026-06-09',
            'estado' => $estados[$id] ?? 'Borrador',
            'notas' => 'Precios pactados para demostración del flujo de cotización.',
        ], $this->totals->calcular($lineas, 800.00));
    }
}
