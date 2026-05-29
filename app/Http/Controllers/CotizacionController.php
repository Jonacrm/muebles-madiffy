<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\CotizacionTotals;
use App\Services\CotizacionVencimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    private const STATUS_LABELS = [
        'borrador' => 'Borrador',
        'creada' => 'Creada',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'convertida' => 'Convertida',
        'rechazada' => 'Rechazada',
        'vencida' => 'Vencida',
    ];

    private const MANUAL_STATUS_VALUES = [
        'borrador',
        'enviada',
    ];

    private const EDITABLE_STATUS_VALUES = [
        'borrador',
        'enviada',
    ];

    private const VALIDITY_DAYS = [3, 7, 14];

    public function __construct(
        private readonly CotizacionTotals $totals,
        private readonly CotizacionVencimiento $vencimiento,
    ) {}

    public function index(): View
    {
        $this->vencimiento->vencerExpiradas();

        $cotizaciones = Quotation::with(['client', 'user'])->orderByDesc('id')->get();

        return view('cotizaciones.index', [
            'cotizaciones' => $cotizaciones->map(fn (Quotation $quotation): array => $this->presentarCotizacion($quotation, false))->all(),
            'resumen' => [
                'pendientes' => Quotation::whereIn('status', ['borrador', 'creada', 'enviada'])->count(),
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
        $data['status'] = 'borrador';

        $quotation = DB::transaction(function () use ($data, $request): Quotation {
            $totales = $this->calcularTotales($data['items'], (float) ($data['discount_global'] ?? 0));

            $this->validarStockDisponible($totales['items'], $data['status']);

            $quotation = new Quotation([
                'folio' => $data['folio'],
                'client_id' => $data['client_id'],
                'user_id' => $request->user()->id,
                'status' => $data['status'],
                'subtotal' => $totales['subtotal'],
                'discount_global' => $totales['discount_global'],
                'tax' => $totales['tax'],
                'total' => $totales['total'],
                'expires_at' => $this->fechaVigencia((int) $data['validity_days']),
                'validity_days' => $data['validity_days'],
            ]);

            $quotation->save();
            $this->guardarLineas($quotation, $totales['items']);
            $this->aplicarMovimientoStock(null, $data['status'], $totales['items']);

            return $quotation;
        });

        return redirect()->route('cotizaciones.show', $quotation)->with('status', 'Cotización guardada correctamente.');
    }

    public function show(string $cotizacion): View
    {
        $this->vencimiento->vencerExpiradas();

        $quotation = Quotation::with(['client', 'user', 'items.product'])->findOrFail($cotizacion);

        return view('cotizaciones.show', [
            'cotizacion' => $this->presentarCotizacion($quotation),
        ]);
    }

    public function edit(string $cotizacion): View|RedirectResponse
    {
        $this->vencimiento->vencerExpiradas();

        $quotation = Quotation::with(['client', 'user', 'items.product'])->findOrFail($cotizacion);

        if (! in_array($quotation->status, self::EDITABLE_STATUS_VALUES, true)) {
            return redirect()
                ->route('cotizaciones.show', $quotation)
                ->with('status', 'Solo las cotizaciones en borrador o enviada pueden editarse.');
        }

        return view('cotizaciones.edit', [
            'cotizacion' => $this->presentarCotizacion($quotation),
            'clientes' => Client::orderBy('name')->get(),
            'productos' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $cotizacion): RedirectResponse
    {
        $quotation = Quotation::findOrFail($cotizacion);

        if (! in_array($quotation->status, self::EDITABLE_STATUS_VALUES, true)) {
            return redirect()
                ->route('cotizaciones.show', $quotation)
                ->with('status', 'Solo las cotizaciones en borrador o enviada pueden editarse.');
        }

        $data = $this->validatedData($request, $quotation->id);
        $data['status'] = $quotation->status;

        DB::transaction(function () use ($quotation, $data): void {
            $oldStatus = $quotation->status;
            $totales = $this->calcularTotales($data['items'], (float) ($data['discount_global'] ?? 0), $quotation);

            $this->validarStockDisponible($totales['items'], $data['status'], $quotation);

            $quotation->fill([
                'folio' => $data['folio'],
                'client_id' => $data['client_id'],
                'status' => $data['status'],
                'subtotal' => $totales['subtotal'],
                'discount_global' => $totales['discount_global'],
                'tax' => $totales['tax'],
                'total' => $totales['total'],
                'expires_at' => $this->resolverFechaVigencia($quotation, $data['status'], (int) $data['validity_days']),
                'validity_days' => $data['validity_days'],
            ]);

            $quotation->save();
            $quotation->items()->delete();
            $this->guardarLineas($quotation, $totales['items']);
            $this->aplicarMovimientoStock($oldStatus, $data['status'], $totales['items']);

            if ($oldStatus === 'enviada' && $data['status'] === 'enviada') {
                $this->ajustarStock($this->lineasNuevas($totales['items']), -1);
            }
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
        $this->vencimiento->vencerExpiradas();

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
                'status' => 'pendiente',
                'subtotal' => $quotation->subtotal,
                'discount_global' => $quotation->discount_global,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'expires_at' => now()->addDays((int) $quotation->validity_days)->toDateString(),
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

    public function cambiarEstado(Request $request, string $cotizacion): RedirectResponse
    {
        $quotation = Quotation::with('items')->findOrFail($cotizacion);
        $data = $request->validate([
            'status' => ['required', Rule::in(['borrador', 'creada', 'enviada', 'aceptada', 'rechazada'])],
        ]);

        if (! $this->transicionPermitida($quotation->status, $data['status'])) {
            return redirect()->route('cotizaciones.show', $quotation)->with('status', 'La transición de estado solicitada no está permitida.');
        }

        DB::transaction(function () use ($quotation, $data): void {
            $oldStatus = $quotation->status;
            $items = $this->itemsDesdeCotizacion($quotation);
            $totales = $this->calcularTotales(
                $items,
                (float) $quotation->discount_global,
                $data['status'] === 'borrador' ? null : $quotation,
            );

            $this->validarStockDisponible($totales['items'], $data['status'], $quotation);

            $quotation->items()->delete();
            $this->guardarLineas($quotation, $totales['items']);

            $quotation->fill([
                'status' => $data['status'],
                'subtotal' => $totales['subtotal'],
                'discount_global' => $totales['discount_global'],
                'tax' => $totales['tax'],
                'total' => $totales['total'],
            ]);

            if (in_array([$oldStatus, $data['status']], [['creada', 'enviada'], ['enviada', 'aceptada']], true)) {
                $quotation->expires_at = $this->fechaVigencia((int) $quotation->validity_days);
            }

            $quotation->save();
            $this->aplicarMovimientoStock($oldStatus, $data['status'], $totales['items']);
        });

        return redirect()
            ->route('cotizaciones.show', $quotation)
            ->with('status', 'Estado de cotización actualizado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $quotationId = null): array
    {
        $this->normalizarDatosNumericos($request);

        return $request->validate([
            'folio' => ['required', 'string', 'max:255', Rule::unique('quotations', 'folio')->ignore($quotationId)],
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['required', Rule::in(self::MANUAL_STATUS_VALUES)],
            'discount_global' => ['nullable', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', Rule::in(self::VALIDITY_DAYS)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.line_discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function calcularTotales(array $items, float $discountGlobal, ?Quotation $quotation = null): array
    {
        $products = Product::whereIn('id', array_column($items, 'product_id'))->get()->keyBy('id');
        $existingItems = $quotation?->items()->get()->keyBy('id') ?? collect();

        if ($quotation?->status === 'enviada') {
            $submittedIds = collect($items)
                ->pluck('id')
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->all();

            $missingExistingItems = $existingItems
                ->reject(fn ($item): bool => in_array((int) $item->id, $submittedIds, true))
                ->map(fn ($item): array => ['id' => $item->id, 'product_id' => $item->product_id])
                ->values()
                ->all();

            $items = array_merge($missingExistingItems, $items);
        }

        $lineas = array_map(function (array $item) use ($products, $existingItems, $quotation): array {
            $existingItem = ! empty($item['id']) ? $existingItems->get((int) $item['id']) : null;

            if ($quotation?->status !== 'borrador' && $existingItem) {
                $productId = (int) $existingItem->product_id;
                $quantity = (int) $existingItem->quantity;
                $unitPrice = (float) $existingItem->unit_price;
                $lineDiscount = (float) $existingItem->line_discount;
                $lineId = (int) $existingItem->id;
            } else {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) ($products->get($productId)?->unit_price ?? 0);
                $lineDiscount = (float) ($item['line_discount'] ?? 0);
                $lineId = ! empty($item['id']) ? (int) $item['id'] : null;
            }

            $subtotal = round(max(($quantity * $unitPrice) - $lineDiscount, 0), 2);

            return [
                'id' => $lineId,
                'product_id' => $productId,
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
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_discount' => $item['line_discount'],
                'subtotal' => $item['subtotal'],
            ]);
        }
    }

    private function normalizarDatosNumericos(Request $request): void
    {
        $items = collect($request->input('items', []))
            ->map(function (array $item): array {
                $item['quantity'] = (int) $this->normalizarNumeroFormulario($item['quantity'] ?? 0);
                $item['line_discount'] = $this->normalizarNumeroFormulario($item['line_discount'] ?? 0);

                return $item;
            })
            ->all();

        $request->merge([
            'discount_global' => $this->normalizarNumeroFormulario($request->input('discount_global', 0)),
            'items' => $items,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     *
     * @throws ValidationException
     */
    private function validarStockDisponible(array $items, string $newStatus, ?Quotation $quotation = null): void
    {
        if (! in_array($newStatus, ['borrador', 'enviada', 'aceptada'], true)) {
            return;
        }

        if ($newStatus === 'aceptada' && $quotation?->status === 'enviada') {
            return;
        }

        $itemsToValidate = collect($items);

        if ($newStatus === 'enviada' && $quotation?->status === 'enviada') {
            $itemsToValidate = $itemsToValidate->filter(fn (array $item): bool => empty($item['id']));
        }

        $quantities = $itemsToValidate
            ->groupBy('product_id')
            ->map(fn ($lines): int => (int) $lines->sum('quantity'));

        if ($quantities->isEmpty()) {
            return;
        }

        $products = Product::whereIn('id', $quantities->keys())->get()->keyBy('id');
        $errors = [];

        foreach ($quantities as $productId => $quantity) {
            $product = $products->get((int) $productId);
            $stock = (int) ($product?->stock ?? 0);

            if ($quantity > $stock) {
                $errors[] = sprintf(
                    'No hay stock suficiente para %s. Disponible: %d, cotizado: %d.',
                    $product?->name ?? 'el producto seleccionado',
                    $stock,
                    $quantity,
                );
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['items' => implode(' ', $errors)]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function aplicarMovimientoStock(?string $oldStatus, string $newStatus, array $items): void
    {
        if (! in_array($oldStatus, ['enviada', 'aceptada'], true) && $newStatus === 'enviada') {
            $this->ajustarStock($items, -1);

            return;
        }

        if (in_array($oldStatus, ['enviada', 'aceptada'], true) && in_array($newStatus, ['rechazada', 'vencida'], true)) {
            $this->ajustarStock($items, 1);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function lineasNuevas(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => empty($item['id']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsDesdeCotizacion(Quotation $quotation): array
    {
        return $quotation->items
            ->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'line_discount' => $item->line_discount,
            ])
            ->all();
    }

    private function transicionPermitida(string $oldStatus, string $newStatus): bool
    {
        return match ($oldStatus) {
            'borrador' => $newStatus === 'creada',
            'creada' => in_array($newStatus, ['borrador', 'enviada'], true),
            'enviada' => in_array($newStatus, ['aceptada', 'rechazada'], true),
            default => false,
        };
    }

    private function fechaVigencia(int $validityDays): string
    {
        return now()->addDays($validityDays)->toDateString();
    }

    private function resolverFechaVigencia(Quotation $quotation, string $newStatus, int $validityDays): string
    {
        if ((int) $quotation->validity_days !== $validityDays || ! $quotation->expires_at) {
            return $this->fechaVigencia($validityDays);
        }

        return $quotation->expires_at->toDateString();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function ajustarStock(array $items, int $direction): void
    {
        collect($items)
            ->groupBy('product_id')
            ->each(function ($lines, int $productId) use ($direction): void {
                $quantity = (int) $lines->sum('quantity');

                if ($direction < 0) {
                    Product::whereKey($productId)->decrement('stock', $quantity);

                    return;
                }

                Product::whereKey($productId)->increment('stock', $quantity);
            });
    }

    private function normalizarNumeroFormulario(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return round(max((float) str_replace(',', '', (string) $value), 0), 2);
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
            'precio_catalogo' => (float) $producto->unit_price,
            'descuento_linea' => 0,
            'line_discount' => 0,
            'stock' => $producto->stock,
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
                'precio_catalogo' => 0,
                'descuento_linea' => 0,
                'line_discount' => 0,
                'stock' => null,
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
            'validity_days' => 14,
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
                'id' => $item->id,
                'product_id' => $item->product_id,
                'sku' => $item->product?->sku,
                'producto' => $item->product?->name ?? 'Producto eliminado',
                'descripcion' => $item->product?->description,
                'cantidad' => $item->quantity,
                'quantity' => $item->quantity,
                'precio_unitario' => (float) $item->unit_price,
                'unit_price' => (float) $item->unit_price,
                'precio_catalogo' => (float) ($item->product?->unit_price ?? $item->unit_price),
                'descuento_linea' => (float) $item->line_discount,
                'line_discount' => (float) $item->line_discount,
                'stock' => $item->product?->stock,
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
            'validity_days' => $quotation->validity_days,
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
