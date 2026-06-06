<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_and_edit_clients_but_cannot_delete_them(): void
    {
        $vendor = User::factory()->create();
        $client = Client::create([
            'name' => 'Cliente base',
            'email' => 'base@example.com',
        ]);

        $payload = [
            'name' => 'Cliente actualizado',
            'email' => 'actualizado@example.com',
            'phone' => '6620000001',
            'rfc' => 'RFC-123456',
            'address' => 'Calle 1',
        ];

        $this->actingAs($vendor)
            ->get(route('clientes.create'))
            ->assertOk();

        $this->actingAs($vendor)
            ->post(route('clientes.store'), $payload)
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clients', [
            'name' => 'Cliente actualizado',
            'email' => 'actualizado@example.com',
        ]);

        $this->actingAs($vendor)
            ->get(route('clientes.edit', $client))
            ->assertOk();

        $this->actingAs($vendor)
            ->patch(route('clientes.update', $client), $payload)
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Cliente actualizado',
        ]);

        $this->actingAs($vendor)
            ->delete(route('clientes.destroy', $client))
            ->assertForbidden();
    }

    public function test_vendor_can_view_own_quotes_but_not_foreign_quotes_or_quote_actions(): void
    {
        $vendor = User::factory()->create();
        $otherVendor = User::factory()->create();
        $ownQuotation = $this->createQuotation($vendor, 'COT-VEN-001', 'borrador');
        $foreignQuotation = $this->createQuotation($otherVendor, 'COT-OTR-001', 'aceptada');

        $this->actingAs($vendor)
            ->get(route('cotizaciones.index'))
            ->assertOk()
            ->assertSee('COT-VEN-001')
            ->assertDontSee('COT-OTR-001');

        $this->actingAs($vendor)
            ->get(route('cotizaciones.show', $ownQuotation))
            ->assertOk();

        $this->actingAs($vendor)
            ->get(route('cotizaciones.show', $foreignQuotation))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->get(route('cotizaciones.edit', $ownQuotation))
            ->assertOk();

        $this->actingAs($vendor)
            ->get(route('cotizaciones.edit', $foreignQuotation))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->patch(route('cotizaciones.estado', $foreignQuotation), ['status' => 'enviada'])
            ->assertForbidden();

        $this->actingAs($vendor)
            ->post(route('cotizaciones.convertir', $foreignQuotation))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->delete(route('cotizaciones.destroy', $ownQuotation))
            ->assertForbidden();
    }

    public function test_vendor_can_view_only_own_orders_and_cannot_change_their_status(): void
    {
        $vendor = User::factory()->create();
        $otherVendor = User::factory()->create();
        $ownOrder = $this->createOrder($vendor, 'PED-VEN-001');
        $foreignOrder = $this->createOrder($otherVendor, 'PED-OTR-001');

        $this->actingAs($vendor)
            ->get(route('pedidos.index'))
            ->assertOk()
            ->assertSee('COT-PED-VEN-001')
            ->assertDontSee('COT-PED-OTR-001');

        $this->actingAs($vendor)
            ->get(route('pedidos.show', $ownOrder))
            ->assertOk();

        $this->actingAs($vendor)
            ->get(route('pedidos.show', $foreignOrder))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->patch(route('pedidos.estado', $ownOrder), ['status' => 'enviado'])
            ->assertForbidden();
    }

    public function test_vendor_can_view_catalog_but_cannot_manage_it(): void
    {
        $vendor = User::factory()->create();
        $product = Product::create([
            'sku' => 'CAT-001',
            'name' => 'Producto catálogo',
            'unit_price' => 100,
            'stock' => 5,
            'active' => true,
        ]);

        $this->actingAs($vendor)
            ->get(route('catalogo.index'))
            ->assertOk()
            ->assertSee('Producto catálogo');

        $payload = [
            'sku' => 'CAT-002',
            'name' => 'Producto nuevo',
            'material' => 'Madera',
            'description' => 'Descripción',
            'unit_price' => '250.00',
            'stock' => 2,
            'active' => '1',
        ];

        $this->actingAs($vendor)
            ->get(route('catalogo.create'))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->post(route('catalogo.store'), $payload)
            ->assertForbidden();

        $this->actingAs($vendor)
            ->get(route('catalogo.edit', $product))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->put(route('catalogo.update', $product), $payload)
            ->assertForbidden();

        $this->actingAs($vendor)
            ->delete(route('catalogo.destroy', $product))
            ->assertForbidden();
    }

    private function createQuotation(User $user, string $folio, string $status): Quotation
    {
        $client = Client::create([
            'name' => 'Cliente '.$folio,
            'email' => strtolower($folio).'@example.com',
        ]);

        $product = Product::create([
            'sku' => $folio.'-SKU',
            'name' => 'Producto '.$folio,
            'unit_price' => 1000,
            'stock' => 10,
            'active' => true,
        ]);

        $quotation = Quotation::create([
            'folio' => $folio,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => 1000,
            'discount_global' => 0,
            'tax' => 160,
            'total' => 1160,
            'expires_at' => now()->addDays(14)->toDateString(),
            'validity_days' => 14,
        ]);

        $quotation->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'line_discount' => 0,
            'subtotal' => 1000,
        ]);

        return $quotation;
    }

    private function createOrder(User $user, string $folio): Order
    {
        $client = Client::create([
            'name' => 'Cliente '.$folio,
            'email' => strtolower($folio).'@example.com',
        ]);

        $product = Product::create([
            'sku' => $folio.'-SKU',
            'name' => 'Producto '.$folio,
            'unit_price' => 100,
            'stock' => 10,
            'active' => true,
        ]);

        $quotation = Quotation::create([
            'folio' => 'COT-'.$folio,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'convertida',
            'subtotal' => 100,
            'discount_global' => 0,
            'tax' => 16,
            'total' => 116,
            'expires_at' => now()->addDays(14)->toDateString(),
            'validity_days' => 14,
        ]);

        $order = Order::create([
            'quotation_id' => $quotation->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'quotation_folio' => $quotation->folio,
            'client_name' => $client->name,
            'client_email' => $client->email,
            'seller_name' => $user->name,
            'status' => 'pendiente',
            'subtotal' => 100,
            'discount_global' => 0,
            'tax' => 16,
            'total' => 116,
            'expires_at' => now()->addDays(14)->toDateString(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 100,
            'line_discount' => 0,
            'subtotal' => 100,
        ]);

        return $order;
    }
}
