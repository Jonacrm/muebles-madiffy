<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotizacionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_converted_status_is_not_available_on_the_edit_form(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'aceptada');

        $response = $this
            ->actingAs($user)
            ->get(route('cotizaciones.edit', $quotation));

        $response
            ->assertOk()
            ->assertDontSee('value="convertida"', false)
            ->assertDontSee('Convertida');
    }

    public function test_quotation_cannot_be_manually_updated_to_converted(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'aceptada');

        $response = $this
            ->actingAs($user)
            ->patch(route('cotizaciones.update', $quotation), [
                'folio' => $quotation->folio,
                'client_id' => $quotation->client_id,
                'status' => 'convertida',
                'discount_global' => 0,
                'expires_at' => now()->addDays(14)->toDateString(),
                'fecha_emision' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $quotation->items()->first()->product_id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'line_discount' => 0,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('status');

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'aceptada',
        ]);
    }

    public function test_accepted_quotation_is_marked_converted_when_the_system_creates_an_order(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'aceptada');

        $response = $this
            ->actingAs($user)
            ->post(route('cotizaciones.convertir', $quotation));

        $order = Order::first();

        $response->assertRedirect(route('pedidos.show', $order));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'convertida',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'quotation_id' => $quotation->id,
        ]);
    }

    private function createQuotation(User $user, string $status): Quotation
    {
        $client = Client::create([
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.com',
        ]);

        $product = Product::create([
            'sku' => 'SKU-TEST',
            'name' => 'Mesa de prueba',
            'unit_price' => 1000,
            'stock' => 5,
            'active' => true,
        ]);

        $quotation = Quotation::create([
            'folio' => 'COT-TEST-001',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => 1000,
            'discount_global' => 0,
            'tax' => 160,
            'total' => 1160,
            'expires_at' => now()->addDays(14)->toDateString(),
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
}
