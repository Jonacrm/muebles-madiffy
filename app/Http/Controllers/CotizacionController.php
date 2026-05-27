<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\CotizacionTotals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    private const STATUS_LABELS = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'convertida' => 'Convertida',
        'rechazada' => 'Rechazada',
        'vencida' => 'Vencida',
    ];

    public function __construct(private readonly CotizacionTotals $totals) {}

    public function index(): View
    {
        $cotizaciones = Quotation::with(['client', 'user'])->latest()->get();

        return view('cotizaciones.index', [
            'cotizaciones' => $cotizaciones->map(fn (Quotation $quotation): array => $this->presentarCotizacion($quotation, false))->all(),
            'resumen' => [
                'pendientes' => Quotation::whereIn('status', ['borrador', 'enviada'])->count(),
                'aceptadas' => Quotation::where('status', 'aceptada')->count(),
                'convertidas' => Quotation::where('status', 'convertida')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('cotizaciones.create', [
            'cotizacion' => $this->cotizacionPlantilla(),
            'clientes' => Client::orderBy('name')->get(),
            'productos' => Product::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $quotation = DB::transaction(function () use ($data, $request): Quotation {
            $totales = $this->calcularTotales($data['items'], (float) ($data['discount_global'] ?? 0));

            $quotation = new Quotation([
                'folio' => $data['folio'],
                'client_id' => $data['client_id'],
                'user_id' => $request->user()->id,
                'status' => $data['status'],
                'subtotal' => $totales['subtotal'],
                'discount_global' => $totales['discount_global'],
                'tax' => $totales['tax'],
                'total' => $totales['total'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            if (! empty($data['fecha_emision'])) {
                $quotation->created_at = $data['fecha_emision'];
            }

            $quotation->save();
            $this->guardarLineas($quotation, $totales['items']);

            return $quotation;
        });

        return redirect()->route('cotizaciones.show', $quotation)->with('status', 'Cotización guardada correctamente.');
    }

    public function show(string $cotizacion): View
    {
        $quotation = Quotation::with(['client', 'user', 'items.product'])->findOrFail($cotizacion);

        return view('cotizaciones.show', [
            'cotizacion' => $this->presentarCotizacion($quotation),
        ]);
    }

    public function edit(string $cotizacion): View
    {
        $quotation = Quotation::with(['client', 'user', 'items.product'])->findOrFail($cotizacion);

        return view('cotizaciones.edit', [
            'cotizacion' => $this->presentarCotizacion($quotation),
            'clientes' => Client::orderBy('name')->get(),
            'productos' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $cotizacion): RedirectResponse
    {
        $quotation = Quotation::findOrFail($cotizacion);
        $data = $this->validatedData($request, $quotation->id);

        DB::transaction(function () use ($quotation, $data): void {
            $totales = $this->calcularTotales($data['items'], (float) ($data['discount_global'] ?? 0));

            $quotation->fill([
                'folio' => $data['folio'],
                'client_id' => $data['client_id'],
                'status' => $data['status'],
                'subtotal' => $totales['subtotal'],
                'discount_global' => $totales['discount_global'],
                'tax' => $totales['tax'],
                'total' => $totales['total'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            if (! empty($data['fecha_emision'])) {
                $quotation->created_at = $data['fecha_emision'];
            }

            $quotation->save();
            $quotation->items()->delete();
            $this->guardarLineas($quotation, $totales['items']);
        });

        return redirect()->route('cotizaciones.show', $quotation)->with('status', 'Cotización actualizada correctamente.');
    }

    public function destroy(string $cotizacion): RedirectResponse
    {
        $quotation = Quotation::findOrFail($cotizacion);

        if ($quotation->order()->exists()) {
            return redirect()->route('cotizaciones.index')->with('status', 'No se puede eliminar una cotización convertida a pedido.');
        }

        $quotation->delete();

        return redirect()->route('cotizaciones.index')->with('status', 'Cotización eliminada correctamente.');
    }

    public function convertir(string $cotizacion): RedirectResponse
    {
        $quotation = Quotation::with(['items', 'order'])->findOrFail($cotizacion);

        if ($quotation->order) {
            return redirect()->route('pedidos.show', $quotation->order)->with('status', 'Esta cotización ya tiene un pedido generado.');
        }

        if ($quotation->status !== 'aceptada') {
            return redirect()->route('cotizaciones.show', $quotation)->with('status', 'Solo una cotización aceptada puede convertirse a pedido.');
        }

        $order = DB::transaction(function () use ($quotation): Order {
            $order = Order::create([
                'quotation_id' => $quotation->id,
                'client_id' => $quotation->client_id,
                'user_id' => $quotation->user_id,
                'status' => 'activo',
                'subtotal' => $quotation->subtotal,
                'discount_global' => $quotation->discount_global,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
            ]);

            foreach ($quotation->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_discount' => $item->line_discount,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $quotation->update(['status' => 'convertida']);

            return $order;
        });

        return redirect()
            ->route('pedidos.show', $order)
            ->with('status', 'Cotización convertida a pedido correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $quotationId = null): array
    {
        return $request->validate([
            'folio' => ['required', 'string', 'max:255', Rule::unique('quotations', 'folio')->ignore($quotationId)],
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'discount_global' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'fecha_emision' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function calcularTotales(array $items, float $discountGlobal): array
    {
        $lineas = array_map(function (array $item): array {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $lineDiscount = (float) ($item['line_discount'] ?? 0);
            $subtotal = round(max(($quantity * $unitPrice) - $lineDiscount, 0), 2);

            return [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'line_discount' => round($lineDiscount, 2),
                'subtotal' => $subtotal,
            ];
        }, $items);

        $subtotal = round(array_sum(array_column($lineas, 'subtotal')), 2);
        $discountGlobal = round($discountGlobal, 2);
        $base = round(max($subtotal - $discountGlobal, 0), 2);
        $tax = round($base * 0.16, 2);

        return [
            'items' => $lineas,
            'subtotal' => $subtotal,
            'discount_global' => $discountGlobal,
            'tax' => $tax,
            'total' => round($base + $tax, 2),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function guardarLineas(Quotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $quotation->items()->create($item);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function cotizacionPlantilla(): array
    {
        $productos = Product::where('active', true)->orderBy('name')->take(2)->get();
        $lineas = $productos->map(fn (Product $producto): array => [
            'product_id' => $producto->id,
            'sku' => $producto->sku,
            'producto' => $producto->name,
            'descripcion' => $producto->description,
            'cantidad' => 1,
            'quantity' => 1,
            'precio_unitario' => (float) $producto->unit_price,
            'unit_price' => (float) $producto->unit_price,
            'descuento_linea' => 0,
            'line_discount' => 0,
            'subtotal' => (float) $producto->unit_price,
        ])->all();

        if ($lineas === []) {
            $lineas[] = [
                'product_id' => null,
                'sku' => null,
                'producto' => 'Selecciona un producto',
                'descripcion' => null,
                'cantidad' => 1,
                'quantity' => 1,
                'precio_unitario' => 0,
                'unit_price' => 0,
                'descuento_linea' => 0,
                'line_discount' => 0,
                'subtotal' => 0,
            ];
        }

        return array_merge([
            'id' => null,
            'folio' => sprintf('COT-%s-%03d', now()->year, Quotation::count() + 1),
            'client_id' => null,
            'user_id' => auth()->id(),
            'cliente' => '',
            'rfc' => '',
            'vendedor' => auth()->user()->name,
            'fecha_emision' => now()->toDateString(),
            'vigencia' => now()->addDays(14)->toDateString(),
            'expires_at' => now()->addDays(14)->toDateString(),
            'status' => 'borrador',
            'estado' => 'Borrador',
            'notas' => '',
        ], $this->totals->calcular($lineas));
    }

    /**
     * @return array<string, mixed>
     */
    private function presentarCotizacion(Quotation $quotation, bool $withLines = true): array
    {
        $lineas = $withLines
            ? $quotation->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'sku' => $item->product?->sku,
                'producto' => $item->product?->name ?? 'Producto eliminado',
                'descripcion' => $item->product?->description,
                'cantidad' => $item->quantity,
                'quantity' => $item->quantity,
                'precio_unitario' => (float) $item->unit_price,
                'unit_price' => (float) $item->unit_price,
                'descuento_linea' => (float) $item->line_discount,
                'line_discount' => (float) $item->line_discount,
                'subtotal' => (float) $item->subtotal,
            ])->all()
            : [];

        $subtotal = (float) $quotation->subtotal;
        $discountGlobal = (float) $quotation->discount_global;

        return [
            'id' => $quotation->id,
            'folio' => $quotation->folio,
            'client_id' => $quotation->client_id,
            'user_id' => $quotation->user_id,
            'cliente' => $quotation->client?->name ?? 'Cliente no disponible',
            'rfc' => $quotation->client?->rfc,
            'vendedor' => $quotation->user?->name ?? 'Usuario no disponible',
            'fecha_emision' => $quotation->created_at?->format('Y-m-d'),
            'vigencia' => $quotation->expires_at?->format('Y-m-d'),
            'expires_at' => $quotation->expires_at?->format('Y-m-d'),
            'status' => $quotation->status,
            'estado' => self::STATUS_LABELS[$quotation->status] ?? ucfirst($quotation->status),
            'notas' => '',
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'discount_global' => $discountGlobal,
            'descuento_global' => $discountGlobal,
            'base' => round(max($subtotal - $discountGlobal, 0), 2),
            'tax' => (float) $quotation->tax,
            'iva' => (float) $quotation->tax,
            'total' => (float) $quotation->total,
        ];
    }
}
