<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotizacionPricingStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_update_uses_current_catalog_price_and_ignores_submitted_price(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'borrador', 50, 1, 10);

        $product->update(['unit_price' => 125]);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.update', $quotation), $this->quotationPayload($quotation, 'borrador', [
                [
                    'id' => $quotation->items()->first()->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 1,
                    'line_discount' => 0,
                ],
            ]))
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $quotation->refresh();

        $this->assertSame('125.00', $quotation->items()->first()->unit_price);
        $this->assertSame('125.00', $quotation->subtotal);
        $this->assertSame('145.00', $quotation->total);
    }

    public function test_sent_quote_keeps_existing_snapshot_price_and_uses_current_catalog_price_for_new_lines(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'enviada', 5, 1, 10);
        $existingItem = $quotation->items()->first();

        $product->update(['unit_price' => 10]);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.update', $quotation), $this->quotationPayload($quotation, 'enviada', [
                [
                    'id' => $existingItem->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'line_discount' => 0,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'line_discount' => 0,
                ],
            ]))
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $prices = $quotation->fresh()->items()->orderBy('unit_price')->pluck('unit_price')->all();

        $this->assertSame(['5.00', '10.00'], $prices);
    }

    public function test_sending_a_created_quote_decrements_stock(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'creada', 100, 3, 7);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), [
                'status' => 'enviada',
            ])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame('enviada', $quotation->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_sending_a_created_quote_is_blocked_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'creada', 100, 10, 7);

        $this
            ->actingAs($user)
            ->from(route('cotizaciones.edit', $quotation))
            ->patch(route('cotizaciones.estado', $quotation), [
                'status' => 'enviada',
            ])
            ->assertRedirect(route('cotizaciones.edit', $quotation))
            ->assertSessionHasErrors('items');

        $this->assertSame('creada', $quotation->fresh()->status);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_rejecting_a_sent_quote_returns_stock(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'enviada', 100, 3, 4);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), [
                'status' => 'rechazada',
            ])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame('rechazada', $quotation->fresh()->status);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_accepted_quote_cannot_be_rejected(): void
    {
        $user = User::factory()->create();
        [$quotation, $product] = $this->createQuotation($user, 'aceptada', 100, 3, 4);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), [
                'status' => 'rechazada',
            ])
            ->assertRedirect(route('cotizaciones.show', $quotation))
            ->assertSessionHas('status', 'La transición de estado solicitada no está permitida.');

        $this->assertSame('aceptada', $quotation->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_catalog_accepts_formatted_price_and_empty_stock_as_zero(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('catalogo.store'), [
                'sku' => 'FMT-001',
                'name' => 'Producto con precio formateado',
                'unit_price' => '1,234.56',
                'stock' => '',
                'active' => '1',
            ])
            ->assertRedirect(route('catalogo.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'FMT-001',
            'unit_price' => 1234.56,
            'stock' => 0,
        ]);
    }

    public function test_catalog_sku_is_locked_on_update_and_duplicate_skus_are_blocked_on_create(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'SKU-LOCKED',
            'name' => 'Producto original',
            'unit_price' => 100,
            'stock' => 1,
            'active' => true,
        ]);
        Product::create([
            'sku' => 'SKU-DUP',
            'name' => 'Producto duplicado',
            'unit_price' => 200,
            'stock' => 1,
            'active' => true,
        ]);

        $this
            ->actingAs($user)
            ->put(route('catalogo.update', $product), [
                'sku' => 'SKU-DUP',
                'name' => 'Producto actualizado',
                'unit_price' => '300.00',
                'stock' => 2,
                'active' => '1',
            ])
            ->assertRedirect(route('catalogo.index'));

        $this->assertSame('SKU-LOCKED', $product->fresh()->sku);

        $this
            ->actingAs($user)
            ->post(route('catalogo.store'), [
                'sku' => 'SKU-DUP',
                'name' => 'Otro producto duplicado',
                'unit_price' => '100.00',
                'stock' => 1,
                'active' => '1',
            ])
            ->assertSessionHasErrors('sku');
    }

    private function createQuotation(User $user, string $status, float $price, int $quantity, int $stock): array
    {
        $client = Client::create(['name' => 'Cliente de prueba']);
        $product = Product::create([
            'sku' => 'PRD-001',
            'name' => 'Silla modelo 1',
            'unit_price' => $price,
            'stock' => $stock,
            'active' => true,
        ]);
        $subtotal = $price * $quantity;
        $tax = round($subtotal * 0.16, 2);

        $quotation = Quotation::create([
            'folio' => 'COT-TEST-001',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_global' => 0,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'expires_at' => now()->addDays(14)->toDateString(),
            'validity_days' => 14,
        ]);

        $quotation->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $price,
            'line_discount' => 0,
            'subtotal' => $subtotal,
        ]);

        return [$quotation, $product];
    }

    private function quotationPayload(Quotation $quotation, string $status, array $items): array
    {
        return [
            'folio' => $quotation->folio,
            'client_id' => $quotation->client_id,
            'status' => $status,
            'discount_global' => 0,
            'validity_days' => 14,
            'expires_at' => now()->addDays(14)->toDateString(),
            'fecha_emision' => now()->toDateString(),
            'items' => $items,
        ];
    }
}
