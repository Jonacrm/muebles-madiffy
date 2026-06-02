<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PedidoVencimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PedidoController extends Controller
{
    private const STATUS_LABELS = [
        'pendiente' => 'Pendiente',
        'enviado' => 'Enviado',
        'vencido' => 'Vencido',
    ];

    public function __construct(private readonly PedidoVencimiento $vencimiento) {}

    public function index(): View
    {
        $this->vencimiento->vencerExpirados();

        $pedidos = Order::with(['client', 'quotation'])->orderByDesc('id')->get();

        return view('pedidos.index', [
            'pedidos' => $pedidos->map(fn (Order $order): array => $this->presentarPedido($order, false))->all(),
        ]);
    }

    public function show(string $pedido): View
    {
        $this->vencimiento->vencerExpirados();

        $order = Order::with(['client', 'quotation', 'user', 'items.product'])->findOrFail($pedido);

        return view('pedidos.show', [
            'pedido' => $this->presentarPedido($order),
        ]);
    }

    public function cambiarEstado(Request $request, string $pedido): RedirectResponse
    {
        $this->vencimiento->vencerExpirados();

        $order = Order::findOrFail($pedido);
        $data = $request->validate([
            'status' => ['required', Rule::in(['enviado'])],
        ]);

        if (! $this->transicionPermitida($order->status, $data['status'])) {
            return redirect()
                ->route('pedidos.show', $order)
                ->with('status', 'La transición de estado solicitada no está permitida.');
        }

        $order->update(['status' => $data['status']]);

        return redirect()
            ->route('pedidos.show', $order)
            ->with('status', 'Estado de pedido actualizado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentarPedido(Order $order, bool $withLines = true): array
    {
        $lineas = $withLines
            ? $order->items->map(fn ($item): array => [
                'sku' => $item->product_sku ?? $item->product?->sku,
                'producto' => $item->product_name ?? $item->product?->name ?? 'Producto eliminado',
                'material' => $item->product_material ?? $item->product?->material,
                'descripcion' => $item->product_description ?? $item->product?->description,
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
            'cotizacion_folio' => $order->quotation_folio ?? $order->quotation?->folio ?? 'Sin cotización',
            'cliente' => $order->client_name ?? $order->client?->name ?? 'Cliente no disponible',
            'cliente_email' => $order->client_email ?? $order->client?->email,
            'cliente_phone' => $order->client_phone ?? $order->client?->phone,
            'cliente_rfc' => $order->client_rfc ?? $order->client?->rfc,
            'cliente_address' => $order->client_address ?? $order->client?->address,
            'vendedor' => $order->seller_name ?? $order->user?->name,
            'fecha_pedido' => $order->created_at?->format('Y-m-d'),
            'status' => $order->status,
            'estado' => self::STATUS_LABELS[$order->status] ?? ucfirst($order->status),
            'expires_at' => $order->expires_at?->format('Y-m-d'),
            'snapshot' => 'Este pedido conserva los datos pactados al convertir la cotización; no depende de cambios posteriores en clientes ni catálogo.',
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'descuento_global' => $discountGlobal,
            'base' => round(max($subtotal - $discountGlobal, 0), 2),
            'iva' => (float) $order->tax,
            'total' => (float) $order->total,
        ];
    }

    private function transicionPermitida(string $oldStatus, string $newStatus): bool
    {
        return match ($oldStatus) {
            'pendiente' => $newStatus === 'enviado',
            default => false,
        };
    }
}
