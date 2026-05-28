<?php

namespace App\Livewire;

use Livewire\Component;

class CotizacionLineItems extends Component
{
    public array $productos = [];

    public array $lineas = [];

    public float|string $discount_global = 0;

    public string $notas = '';

    public function mount($productosIniciales = [], array $lineasIniciales = [], mixed $descuentoGlobalInicial = 0, string $notasIniciales = ''): void
    {
        $this->productos = collect($productosIniciales)
            ->map(fn ($producto): array => [
                'id' => (int) data_get($producto, 'id'),
                'sku' => data_get($producto, 'sku'),
                'name' => data_get($producto, 'name'),
                'description' => data_get($producto, 'description'),
                'unit_price' => $this->normalizarNumero(data_get($producto, 'unit_price', 0)),
            ])
            ->values()
            ->all();

        $this->lineas = collect($lineasIniciales)
            ->map(fn (array $linea): array => $this->normalizarLinea($linea))
            ->values()
            ->all();

        if ($this->lineas === []) {
            $this->agregarLinea();
        }

        $this->discount_global = $this->normalizarNumero($descuentoGlobalInicial);
        $this->notas = $notasIniciales;
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

        $producto = $this->productoPorId($productId);

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
            'line_discount' => 0,
        ];
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

    private function base(): float
    {
        return round(max($this->subtotal() - $this->descuentoGlobal(), 0), 2);
    }

    private function normalizarLinea(array $linea): array
    {
        $producto = $this->productoPorId($linea['product_id'] ?? null);
        $cantidad = (int) ($linea['quantity'] ?? $linea['cantidad'] ?? 1);
        $precioUnitario = $linea['unit_price'] ?? $linea['precio_unitario'] ?? $producto['unit_price'] ?? 0;
        $descuentoLinea = $linea['line_discount'] ?? $linea['descuento_linea'] ?? 0;

        return [
            'product_id' => $producto['id'] ?? ($linea['product_id'] ?? null),
            'sku' => $producto['sku'] ?? ($linea['sku'] ?? null),
            'producto' => $producto['name'] ?? ($linea['producto'] ?? 'Selecciona un producto'),
            'descripcion' => $producto['description'] ?? ($linea['descripcion'] ?? null),
            'quantity' => max($cantidad, 1),
            'unit_price' => $this->normalizarNumero($precioUnitario),
            'line_discount' => $this->normalizarNumero($descuentoLinea),
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
            'line_discount' => 0,
        ];
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

    private function normalizarNumero(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return round(max((float) $value, 0), 2);
    }
}
