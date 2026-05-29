<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class CotizacionLineItems extends Component
{
    public array $productos = [];

    public array $lineas = [];

    public float|string $discount_global = 0;

    public string $status = 'borrador';

    public function mount($productosIniciales = [], array $lineasIniciales = [], mixed $descuentoGlobalInicial = 0, string $status = 'borrador'): void
    {
        $this->productos = collect($productosIniciales)
            ->map(fn ($producto): array => $this->normalizarProducto($producto))
            ->values()
            ->all();

        $this->status = $status;

        $this->lineas = collect($lineasIniciales)
            ->map(fn (array $linea): array => $this->normalizarLinea($linea))
            ->values()
            ->all();

        if ($this->lineas === []) {
            $this->agregarLinea();
        }

        $this->discount_global = $this->normalizarNumero($descuentoGlobalInicial);
    }

    public function agregarLinea(): void
    {
        $this->lineas[] = $this->lineaVacia();
    }

    public function quitarLinea(int $index): void
    {
        if (! isset($this->lineas[$index])) {
            return;
        }

        unset($this->lineas[$index]);
        $this->lineas = array_values($this->lineas);

        if ($this->lineas === []) {
            $this->agregarLinea();
        }
    }

    public function seleccionarProducto(int $index, mixed $productId): void
    {
        if (! isset($this->lineas[$index])) {
            return;
        }

        if ($this->lineaBloqueada($this->lineas[$index])) {
            return;
        }

        $producto = $this->productoActualPorId($productId);

        if ($producto === null) {
            $this->lineas[$index] = $this->lineaVacia();

            return;
        }

        $this->lineas[$index] = [
            'product_id' => $producto['id'],
            'sku' => $producto['sku'],
            'producto' => $producto['name'],
            'descripcion' => $producto['description'],
            'quantity' => 1,
            'unit_price' => $producto['unit_price'],
            'precio_cotizado' => null,
            'precio_catalogo' => $producto['unit_price'],
            'line_discount' => 0,
            'stock' => $producto['stock'],
            'locked' => false,
        ];
    }

    public function refrescarPreciosDeCatalogo(): void
    {
        if ($this->status !== 'borrador') {
            return;
        }

        $ids = collect($this->lineas)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $productos = Product::whereIn('id', $ids)->get()->keyBy('id');

        foreach ($this->lineas as $index => $linea) {
            $producto = $productos->get((int) ($linea['product_id'] ?? 0));

            if (! $producto) {
                continue;
            }

            $productoNormalizado = $this->normalizarProducto($producto);
            $this->lineas[$index]['sku'] = $productoNormalizado['sku'];
            $this->lineas[$index]['producto'] = $productoNormalizado['name'];
            $this->lineas[$index]['descripcion'] = $productoNormalizado['description'];
            $this->lineas[$index]['unit_price'] = $productoNormalizado['unit_price'];
            $this->lineas[$index]['precio_catalogo'] = $productoNormalizado['unit_price'];
            $this->lineas[$index]['stock'] = $productoNormalizado['stock'];
        }
    }

    public function subtotalLinea(array $linea): float
    {
        $cantidad = max((float) ($linea['quantity'] ?? 0), 0);
        $precioUnitario = $this->normalizarNumero($linea['unit_price'] ?? 0);
        $descuentoLinea = $this->normalizarNumero($linea['line_discount'] ?? 0);

        return round(max(($cantidad * $precioUnitario) - $descuentoLinea, 0), 2);
    }

    public function subtotal(): float
    {
        return round(array_sum(array_map(fn (array $linea): float => $this->subtotalLinea($linea), $this->lineas)), 2);
    }

    public function descuentoGlobal(): float
    {
        return $this->normalizarNumero($this->discount_global);
    }

    public function iva(): float
    {
        return round($this->base() * 0.16, 2);
    }

    public function total(): float
    {
        return round($this->base() + $this->iva(), 2);
    }

    public function render()
    {
        return view('livewire.cotizacion-line-items');
    }

    public function cambiosPrecio(): array
    {
        if ($this->status !== 'borrador') {
            return [];
        }

        return collect($this->lineas)
            ->filter(function (array $linea): bool {
                if (($linea['precio_cotizado'] ?? null) === null) {
                    return false;
                }

                return $this->normalizarNumero($linea['precio_cotizado']) !== $this->normalizarNumero($linea['precio_catalogo'] ?? $linea['unit_price'] ?? 0);
            })
            ->map(fn (array $linea): array => [
                'producto' => $linea['producto'] ?? 'Producto',
                'precio_anterior' => $this->normalizarNumero($linea['precio_cotizado']),
                'precio_actual' => $this->normalizarNumero($linea['precio_catalogo'] ?? $linea['unit_price'] ?? 0),
            ])
            ->values()
            ->all();
    }

    public function lineaBloqueada(array $linea): bool
    {
        return $this->status === 'enviada' && ! empty($linea['id']);
    }

    private function base(): float
    {
        return round(max($this->subtotal() - $this->descuentoGlobal(), 0), 2);
    }

    private function normalizarLinea(array $linea): array
    {
        $producto = $this->productoPorId($linea['product_id'] ?? null);
        $cantidad = (int) ($linea['quantity'] ?? $linea['cantidad'] ?? 1);
        $precioCotizado = $linea['unit_price'] ?? $linea['precio_unitario'] ?? null;
        $precioCatalogo = $producto['unit_price'] ?? $precioCotizado ?? 0;
        $precioUnitario = $this->status === 'borrador'
            ? $precioCatalogo
            : ($precioCotizado ?? $precioCatalogo);
        $descuentoLinea = $linea['line_discount'] ?? $linea['descuento_linea'] ?? 0;

        return [
            'id' => $linea['id'] ?? null,
            'product_id' => $producto['id'] ?? ($linea['product_id'] ?? null),
            'sku' => $producto['sku'] ?? ($linea['sku'] ?? null),
            'producto' => $producto['name'] ?? ($linea['producto'] ?? 'Selecciona un producto'),
            'descripcion' => $producto['description'] ?? ($linea['descripcion'] ?? null),
            'quantity' => max($cantidad, 1),
            'unit_price' => $this->normalizarNumero($precioUnitario),
            'precio_cotizado' => $precioCotizado === null ? null : $this->normalizarNumero($precioCotizado),
            'precio_catalogo' => $this->normalizarNumero($precioCatalogo),
            'line_discount' => $this->normalizarNumero($descuentoLinea),
            'stock' => $producto['stock'] ?? null,
            'locked' => $this->status === 'enviada' && ! empty($linea['id']),
        ];
    }

    private function lineaVacia(): array
    {
        return [
            'product_id' => null,
            'sku' => null,
            'producto' => 'Selecciona un producto',
            'descripcion' => null,
            'quantity' => 1,
            'unit_price' => 0,
            'precio_cotizado' => null,
            'precio_catalogo' => 0,
            'line_discount' => 0,
            'stock' => null,
            'locked' => false,
        ];
    }

    private function productoActualPorId(mixed $productId): ?array
    {
        if ($productId === null || $productId === '') {
            return null;
        }

        $producto = Product::find($productId);

        return $producto ? $this->normalizarProducto($producto) : $this->productoPorId($productId);
    }

    private function productoPorId(mixed $productId): ?array
    {
        if ($productId === null || $productId === '') {
            return null;
        }

        foreach ($this->productos as $producto) {
            if ((int) $producto['id'] === (int) $productId) {
                return $producto;
            }
        }

        return null;
    }

    private function normalizarProducto(mixed $producto): array
    {
        return [
            'id' => (int) data_get($producto, 'id'),
            'sku' => data_get($producto, 'sku'),
            'name' => data_get($producto, 'name'),
            'description' => data_get($producto, 'description'),
            'unit_price' => $this->normalizarNumero(data_get($producto, 'unit_price', 0)),
            'stock' => data_get($producto, 'stock'),
        ];
    }

    private function normalizarNumero(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return round(max((float) $value, 0), 2);
    }
}
