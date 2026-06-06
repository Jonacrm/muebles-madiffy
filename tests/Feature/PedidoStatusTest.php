<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PedidoStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_order_can_be_marked_sent(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrder($user, 'pendiente');

        $this
            ->actingAs($user)
            ->patch(route('pedidos.estado', $order), ['status' => 'enviado'])
            ->assertRedirect(route('pedidos.show', $order));

        $this->assertSame('enviado', $order->fresh()->status);
    }

    public function test_pending_order_expires_and_returns_stock(): void
    {
        $user = User::factory()->create();
        [$order, $product] = $this->createOrder($user, 'pendiente', 3, 2, now()->subDay()->toDateString());

        Artisan::call('pedidos:vencer');

        $this->assertSame('vencido', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_sent_order_does_not_expire_by_expiration_deadline(): void
    {
        $user = User::factory()->create();
        [$order, $product] = $this->createOrder($user, 'enviado', 3, 2, now()->subDay()->toDateString());

        Artisan::call('pedidos:vencer');

        $this->assertSame('enviado', $order->fresh()->status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_order_created_from_quotation_starts_pending_with_payment_deadline(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createAcceptedQuotation($user, 7);

        $this
            ->actingAs($user)
            ->post(route('cotizaciones.convertir', $quotation))
            ->assertRedirect();

        $order = Order::first();

        $this->assertSame('pendiente', $order->status);
        $this->assertSame(now()->addDays(7)->toDateString(), $order->expires_at->toDateString());
    }

    public function test_order_snapshot_stays_sealed_after_catalog_client_and_quote_changes(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createAcceptedQuotation($user, 7);
        $product = $quotation->items()->first()->product;
        $client = $quotation->client;

        $this
            ->actingAs($user)
            ->post(route('cotizaciones.convertir', $quotation))
            ->assertRedirect();

        $order = Order::with('items')->first();

        $product->update([
            'sku' => 'QUOTE-PRD-CHANGED',
            'name' => 'Producto actualizado',
            'material' => 'Material actualizado',
            'description' => 'Descripción actualizada',
            'unit_price' => 999,
        ]);
        $client->update([
            'name' => 'Cliente actualizado',
            'rfc' => 'RFC-NUEVO',
        ]);
        $quotation->update(['folio' => 'COT-CONVERT-CHANGED']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'quotation_folio' => 'COT-CONVERT-001',
            'client_name' => 'Cliente de prueba',
            'client_rfc' => 'RFC-ORIGINAL',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_sku' => 'QUOTE-PRD-001',
            'product_name' => 'Producto cotizado',
            'product_material' => 'Madera original',
            'product_description' => 'Descripción original',
            'unit_price' => 100,
        ]);

        $this
            ->actingAs($user)
            ->get(route('pedidos.show', $order))
            ->assertOk()
            ->assertSee('COT-CONVERT-001')
            ->assertSee('Cliente de prueba')
            ->assertSee('RFC-ORIGINAL')
            ->assertSee('QUOTE-PRD-001')
            ->assertSee('Producto cotizado')
            ->assertSee('Madera original')
            ->assertSee('Descripción original')
            ->assertDontSee('COT-CONVERT-CHANGED')
            ->assertDontSee('Cliente actualizado')
            ->assertDontSee('QUOTE-PRD-CHANGED')
            ->assertDontSee('Producto actualizado')
            ->assertDontSee('Material actualizado')
            ->assertDontSee('Descripción actualizada');
    }

    private function createOrder(User $user, string $status, int $quantity = 1, int $stock = 10, ?string $expiresAt = null): array|Order
    {
        $client = Client::create(['name' => 'Cliente de prueba']);
        $product = Product::create([
            'sku' => 'PED-PRD-001',
            'name' => 'Producto pedido',
            'unit_price' => 100,
            'stock' => $stock,
            'active' => true,
        ]);
        $quotation = Quotation::create([
            'folio' => 'COT-PED-001',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'convertida',
            'subtotal' => 100 * $quantity,
            'discount_global' => 0,
            'tax' => 16 * $quantity,
            'total' => 116 * $quantity,
            'expires_at' => now()->addDays(14)->toDateString(),
            'validity_days' => 14,
        ]);
        $order = Order::create([
            'quotation_id' => $quotation->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => 100 * $quantity,
            'discount_global' => 0,
            'tax' => 16 * $quantity,
            'total' => 116 * $quantity,
            'expires_at' => $expiresAt ?? now()->addDays(14)->toDateString(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => 100,
            'line_discount' => 0,
            'subtotal' => 100 * $quantity,
        ]);

        return $quantity === 1 && $stock === 10 && $expiresAt === null ? $order : [$order, $product];
    }

    private function createAcceptedQuotation(User $user, int $validityDays): Quotation
    {
        $client = Client::create([
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.com',
            'phone' => '6620000000',
            'rfc' => 'RFC-ORIGINAL',
            'address' => 'Dirección original',
        ]);
        $product = Product::create([
            'sku' => 'QUOTE-PRD-001',
            'name' => 'Producto cotizado',
            'material' => 'Madera original',
            'description' => 'Descripción original',
            'unit_price' => 100,
            'stock' => 10,
            'active' => true,
        ]);
        $quotation = Quotation::create([
            'folio' => 'COT-CONVERT-001',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'aceptada',
            'subtotal' => 100,
            'discount_global' => 0,
            'tax' => 16,
            'total' => 116,
            'expires_at' => now()->addDays($validityDays)->toDateString(),
            'validity_days' => $validityDays,
        ]);

        $quotation->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'line_discount' => 0,
            'subtotal' => 100,
        ]);

        return $quotation;
    }
}
