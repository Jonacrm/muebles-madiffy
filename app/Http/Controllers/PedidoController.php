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
        'pagado' => 'Pagado',
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

        $order = Order::with(['client', 'quotation', 'items.product'])->findOrFail($pedido);

        return view('pedidos.show', [
            'pedido' => $this->presentarPedido($order),
        ]);
    }

    public function cambiarEstado(Request $request, string $pedido): RedirectResponse
    {
        $this->vencimiento->vencerExpirados();

        $order = Order::findOrFail($pedido);
        $data = $request->validate([
            'status' => ['required', Rule::in(['pagado', 'enviado'])],
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
            'status' => $order->status,
            'estado' => self::STATUS_LABELS[$order->status] ?? ucfirst($order->status),
            'expires_at' => $order->expires_at?->format('Y-m-d'),
            'snapshot' => 'Este pedido copia los conceptos y precios pactados de la cotización; no depende del precio actual del catálogo.',
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
            'pendiente' => $newStatus === 'pagado',
            'pagado' => $newStatus === 'enviado',
            default => false,
        };
    }
}
