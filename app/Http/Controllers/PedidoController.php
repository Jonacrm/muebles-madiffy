<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(): View
    {
        $pedidos = Order::with(['client', 'quotation'])->latest()->get();

        return view('pedidos.index', [
            'pedidos' => $pedidos->map(fn (Order $order): array => $this->presentarPedido($order, false))->all(),
        ]);
    }

    public function show(string $pedido): View
    {
        $order = Order::with(['client', 'quotation', 'items.product'])->findOrFail($pedido);

        return view('pedidos.show', [
            'pedido' => $this->presentarPedido($order),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentarPedido(Order $order, bool $withLines = true): array
    {
        $lineas = $withLines
            ? $order->items->map(fn ($item): array => [
                'sku' => $item->product?->sku,
                'producto' => $item->product?->name ?? 'Producto eliminado',
                'descripcion' => $item->product?->description,
                'cantidad' => $item->quantity,
                'precio_unitario' => (float) $item->unit_price,
                'descuento_linea' => (float) $item->line_discount,
                'subtotal' => (float) $item->subtotal,
            ])->all()
            : [];

        $subtotal = (float) $order->subtotal;
        $discountGlobal = (float) $order->discount_global;

        return [
            'id' => $order->id,
            'folio' => sprintf('PED-%s-%03d', $order->created_at?->format('Y') ?? now()->year, $order->id),
            'cotizacion_id' => $order->quotation_id,
            'cotizacion_folio' => $order->quotation?->folio ?? 'Sin cotización',
            'cliente' => $order->client?->name ?? 'Cliente no disponible',
            'fecha_pedido' => $order->created_at?->format('Y-m-d'),
            'estado' => ucfirst($order->status),
            'snapshot' => 'Este pedido copia los conceptos y precios pactados de la cotización; no depende del precio actual del catálogo.',
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'descuento_global' => $discountGlobal,
            'base' => round(max($subtotal - $discountGlobal, 0), 2),
            'iva' => (float) $order->tax,
            'total' => (float) $order->total,
        ];
    }
}
