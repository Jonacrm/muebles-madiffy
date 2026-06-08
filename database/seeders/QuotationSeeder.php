<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Services\CotizacionTotals;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $totals = new CotizacionTotals;

        DB::transaction(function () use ($totals): void {
            $seller = User::where('role', 'vendedor')->firstOrFail();

            $this->crearCotizacion(
                totals: $totals,
                folio: 'COT-2026-001',
                client: Client::where('email', 'compras@constructoranor.com')->firstOrFail(),
                seller: $seller,
                status: 'borrador',
                discountGlobal: 0,
                validityDays: 15,
                expiresAt: now()->addDays(15)->toDateString(),
                lineas: [
                    [
                        'product' => Product::where('sku', 'MUE-001')->firstOrFail(),
                        'quantity' => 10,
                        'line_discount' => 0,
                    ],
                    [
                        'product' => Product::where('sku', 'MUE-002')->firstOrFail(),
                        'quantity' => 2,
                        'line_discount' => 500,
                    ],
                ],
            );

            $this->crearCotizacion(
                totals: $totals,
                folio: 'COT-2026-002',
                client: Client::where('email', 'administracion@sonorainn.com')->firstOrFail(),
                seller: $seller,
                status: 'aceptada',
                discountGlobal: 1000,
                validityDays: 30,
                expiresAt: now()->addDays(30)->toDateString(),
                lineas: [
                    [
                        'product' => Product::where('sku', 'MUE-003')->firstOrFail(),
                        'quantity' => 1,
                        'line_discount' => 0,
                    ],
                    [
                        'product' => Product::where('sku', 'MUE-004')->firstOrFail(),
                        'quantity' => 10,
                        'line_discount' => 0,
                    ],
                ],
            );

            $this->crearCotizacion(
                totals: $totals,
                folio: 'COT-2026-003',
                client: Client::where('email', 'contacto@elmesquite.com')->firstOrFail(),
                seller: $seller,
                status: 'vencida',
                discountGlobal: 0,
                validityDays: 14,
                expiresAt: now()->subDays(5)->toDateString(),
                lineas: [
                    [
                        'product' => Product::where('sku', 'MUE-007')->firstOrFail(),
                        'quantity' => 2,
                        'line_discount' => 0,
                    ],
                ],
            );
        });
    }

    /**
     * @param  array<int, array{product: Product, quantity: int, line_discount: float|int}>  $lineas
     */
    private function crearCotizacion(
        CotizacionTotals $totals,
        string $folio,
        Client $client,
        User $seller,
        string $status,
        float $discountGlobal,
        int $validityDays,
        string $expiresAt,
        array $lineas,
    ): void {
        $lineasTotales = collect($lineas)
            ->map(fn (array $linea): array => [
                'product_id' => $linea['product']->id,
                'cantidad' => (int) $linea['quantity'],
                'precio_unitario' => (float) $linea['product']->unit_price,
                'descuento_linea' => (float) $linea['line_discount'],
            ])
            ->all();

        $totalesCalculados = $totals->calcular($lineasTotales, $discountGlobal);

        $quotation = Quotation::create([
            'folio' => $folio,
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'status' => $status,
            'subtotal' => $totalesCalculados['subtotal'],
            'discount_global' => $totalesCalculados['descuento_global'],
            'tax' => $totalesCalculados['iva'],
            'total' => $totalesCalculados['total'],
            'expires_at' => $expiresAt,
            'validity_days' => $validityDays,
        ]);

        foreach ($totalesCalculados['lineas'] as $linea) {
            $quotation->items()->create([
                'product_id' => $linea['product_id'],
                'quantity' => $linea['cantidad'],
                'unit_price' => $linea['precio_unitario'],
                'line_discount' => $linea['descuento_linea'],
                'subtotal' => $linea['subtotal'],
            ]);
        }

        if (in_array($status, ['creada', 'enviada', 'aceptada'], true)) {
            foreach ($lineasTotales as $linea) {
                Product::whereKey($linea['product_id'])->decrement('stock', $linea['cantidad']);
            }
        }
    }
}
