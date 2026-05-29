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

class CotizacionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_converted_status_is_not_available_on_the_edit_form(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'borrador');

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
        $quotation = $this->createQuotation($user, 'borrador');

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
            'status' => 'borrador',
        ]);
    }

    public function test_accepted_quotation_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'aceptada');

        $response = $this
            ->actingAs($user)
            ->get(route('cotizaciones.edit', $quotation));

        $response
            ->assertRedirect(route('cotizaciones.show', $quotation))
            ->assertSessionHas('status', 'Solo las cotizaciones en borrador o enviada pueden editarse.');
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

    public function test_store_always_creates_a_draft_quote(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Cliente de prueba']);
        $product = Product::create([
            'sku' => 'SKU-CREATE',
            'name' => 'Mesa de prueba',
            'unit_price' => 1000,
            'stock' => 5,
            'active' => true,
        ]);

        $this
            ->actingAs($user)
            ->post(route('cotizaciones.store'), [
                'folio' => 'COT-CREATE-001',
                'client_id' => $client->id,
                'status' => 'enviada',
                'discount_global' => 0,
                'validity_days' => 7,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'line_discount' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quotations', [
            'folio' => 'COT-CREATE-001',
            'status' => 'borrador',
            'validity_days' => 7,
        ]);
    }

    public function test_draft_can_be_created_and_created_can_return_to_draft(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'borrador');

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), ['status' => 'creada'])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame('creada', $quotation->fresh()->status);

        $this
            ->actingAs($user)
            ->get(route('cotizaciones.edit', $quotation))
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), ['status' => 'borrador'])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame('borrador', $quotation->fresh()->status);
    }

    public function test_sending_and_accepting_renew_validity(): void
    {
        $user = User::factory()->create();
        $quotation = $this->createQuotation($user, 'creada');
        $quotation->update([
            'validity_days' => 3,
            'expires_at' => now()->subDay()->toDateString(),
        ]);

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), ['status' => 'enviada'])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame(now()->addDays(3)->toDateString(), $quotation->fresh()->expires_at->toDateString());

        $this
            ->actingAs($user)
            ->patch(route('cotizaciones.estado', $quotation), ['status' => 'aceptada'])
            ->assertRedirect(route('cotizaciones.show', $quotation));

        $this->assertSame(now()->addDays(3)->toDateString(), $quotation->fresh()->expires_at->toDateString());
    }

    public function test_expiration_command_only_expires_sent_and_accepted_quotes_and_returns_stock(): void
    {
        $user = User::factory()->create();
        $draft = $this->createQuotation($user, 'borrador');
        $sent = $this->createQuotation($user, 'enviada', 'COT-TEST-002');
        $accepted = $this->createQuotation($user, 'aceptada', 'COT-TEST-003');

        $draft->update(['expires_at' => now()->subDay()->toDateString()]);
        $sent->update(['expires_at' => now()->subDay()->toDateString()]);
        $accepted->update(['expires_at' => now()->subDay()->toDateString()]);

        Artisan::call('cotizaciones:vencer');

        $this->assertSame('borrador', $draft->fresh()->status);
        $this->assertSame('vencida', $sent->fresh()->status);
        $this->assertSame('vencida', $accepted->fresh()->status);
    }

    private function createQuotation(User $user, string $status, string $folio = 'COT-TEST-001'): Quotation
    {
        $client = Client::create([
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.com',
        ]);

        $product = Product::create([
            'sku' => $folio,
            'name' => 'Mesa de prueba',
            'unit_price' => 1000,
            'stock' => 5,
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
}
