<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const QUOTATION_STATUS_LABELS = [
        'borrador' => 'Borrador',
        'creada' => 'Creada',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'convertida' => 'Convertida',
        'rechazada' => 'Rechazada',
        'vencida' => 'Vencida',
    ];

    private const ORDER_STATUS_LABELS = [
        'pendiente' => 'Pendiente',
        'enviado' => 'Enviado',
        'vencido' => 'Vencido',
    ];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $stockThreshold = 5;

        $quotationQuery = Quotation::query();
        $orderQuery = Order::query();

        if (! Gate::allows('ver-todas-cotizaciones')) {
            $quotationQuery->where('user_id', $user->id);
        }

        if (! Gate::allows('ver-todos-pedidos')) {
            $orderQuery->where('user_id', $user->id);
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $formalQuoteStatuses = ['enviada', 'aceptada', 'convertida'];
        $pendingQuoteStatuses = ['borrador', 'creada', 'enviada'];

        $metrics = [
            'total_cotizado_mes' => (float) (clone $quotationQuery)
                ->whereIn('status', $formalQuoteStatuses)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total'),
            'valor_pedidos_mes' => (float) (clone $orderQuery)
                ->whereIn('status', ['pendiente', 'enviado'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total'),
            'cotizaciones_pendientes' => (clone $quotationQuery)
                ->whereIn('status', $pendingQuoteStatuses)
                ->count(),
            'pedidos_pendientes' => (clone $orderQuery)
                ->where('status', 'pendiente')
                ->count(),
            'cotizaciones_por_vencer' => (clone $quotationQuery)
                ->whereIn('status', ['enviada', 'aceptada'])
                ->whereDate('expires_at', '>=', today())
                ->whereDate('expires_at', '<=', today()->addDays(3))
                ->count(),
            'cotizaciones_vencidas' => (clone $quotationQuery)
                ->where('status', 'vencida')
                ->count(),
            'pedidos_vencidos' => (clone $orderQuery)
                ->where('status', 'vencido')
                ->count(),
            'productos_bajo_stock' => Product::where('active', true)
                ->where('stock', '<=', $stockThreshold)
                ->count(),
            'productos_sin_stock' => Product::where('active', true)
                ->where('stock', '<=', 0)
                ->count(),
        ];

        $ultimasCotizaciones = (clone $quotationQuery)
            ->with(['client'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Quotation $quotation): array => [
                'id' => $quotation->id,
                'folio' => $quotation->folio,
                'cliente' => $quotation->client_name ?? $quotation->client?->name ?? 'Cliente no disponible',
                'estado' => self::QUOTATION_STATUS_LABELS[$quotation->status] ?? ucfirst($quotation->status),
                'status' => $quotation->status,
                'total' => (float) $quotation->total,
            ])
            ->all();

        $productosBajoStock = Product::where('active', true)
            ->where('stock', '<=', $stockThreshold)
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'sku', 'name', 'stock'])
            ->map(fn (Product $product): array => [
                'sku' => $product->sku,
                'name' => $product->name,
                'stock' => (int) $product->stock,
            ])
            ->all();

        return view('dashboard', [
            'metrics' => $metrics,
            'ultimasCotizaciones' => $ultimasCotizaciones,
            'productosBajoStock' => $productosBajoStock,
            'stockThreshold' => $stockThreshold,
        ]);
    }
}
