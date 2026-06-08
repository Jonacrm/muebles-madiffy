<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_global_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = User::factory()->create();
        $otherVendor = User::factory()->create();

        $this->createQuotation($vendor, 'COT-ADM-001', 'enviada', 100, now()->addDays(10));
        $this->createQuotation($otherVendor, 'COT-ADM-002', 'aceptada', 200, now()->addDays(2));
        $this->createQuotation($vendor, 'COT-ADM-003', 'borrador', 300, now()->addDays(14));
        $this->createQuotation($otherVendor, 'COT-ADM-004', 'vencida', 400, now()->subDay());

        $this->createOrder($vendor, 'PED-ADM-001', 'pendiente', 500);
        $this->createOrder($otherVendor, 'PED-ADM-002', 'enviado', 600);
        $this->createOrder($otherVendor, 'PED-ADM-003', 'vencido', 700);

        Product::create([
            'sku' => 'LOW-001',
            'name' => 'Producto bajo stock',
            'unit_price' => 100,
            'stock' => 2,
            'active' => true,
        ]);
        Product::create([
            'sku' => 'ZERO-001',
            'name' => 'Producto sin stock',
            'unit_price' => 100,
            'stock' => 0,
            'active' => true,
        ]);
        Product::create([
            'sku' => 'INACTIVE-LOW',
            'name' => 'Producto inactivo bajo stock',
            'unit_price' => 100,
            'stock' => 1,
            'active' => false,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSeeText('Métricas globales')
            ->assertSeeText('Total cotizado del mes')
            ->assertSeeText('$300.00')
            ->assertSeeText('Valor pedidos activos del mes')
            ->assertSeeText('$1,100.00')
            ->assertSeeText('Cotizaciones por vencer')
            ->assertSeeText('Stock crítico')
            ->assertSeeText('1 sin stock, límite 5')
            ->assertSeeText('COT-ADM-004')
            ->assertSeeText('Producto bajo stock')
            ->assertSeeText('Producto sin stock')
            ->assertDontSeeText('Producto inactivo bajo stock');
    }

    public function test_vendor_dashboard_only_uses_own_sales_metrics(): void
    {
        $vendor = User::factory()->create();
        $otherVendor = User::factory()->create();

        $this->createQuotation($vendor, 'COT-OWN-001', 'enviada', 1234, now()->addDays(14));
        $this->createQuotation($otherVendor, 'COT-FOREIGN-001', 'enviada', 4321, now()->addDays(14));
        $this->createOrder($vendor, 'PED-OWN-001', 'pendiente', 222);
        $this->createOrder($otherVendor, 'PED-FOREIGN-001', 'pendiente', 333);

        $this
            ->actingAs($vendor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSeeText('Mis métricas')
            ->assertSeeText('$1,234.00')
            ->assertSeeText('$222.00')
            ->assertSeeText('COT-OWN-001')
            ->assertDontSeeText('$4,321.00')
            ->assertDontSeeText('$333.00')
            ->assertDontSeeText('COT-FOREIGN-001');
    }

    private function createQuotation(User $user, string $folio, string $status, float $total, mixed $expiresAt): Quotation
    {
        $client = Client::create([
            'name' => 'Cliente '.$folio,
            'email' => strtolower($folio).'@example.com',
        ]);
        $subtotal = round($total / 1.16, 2);
        $tax = round($total - $subtotal, 2);

        return Quotation::create([
            'folio' => $folio,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_global' => 0,
            'tax' => $tax,
            'total' => $total,
            'expires_at' => $expiresAt,
            'validity_days' => 14,
        ]);
    }

    private function createOrder(User $user, string $folio, string $status, float $total): Order
    {
        $quotation = $this->createQuotation($user, 'COT-'.$folio, 'convertida', 0, now()->addDays(14));
        $subtotal = round($total / 1.16, 2);
        $tax = round($total - $subtotal, 2);

        return Order::create([
            'quotation_id' => $quotation->id,
            'client_id' => $quotation->client_id,
            'user_id' => $user->id,
            'quotation_folio' => $quotation->folio,
            'client_name' => $quotation->client?->name,
            'seller_name' => $user->name,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_global' => 0,
            'tax' => $tax,
            'total' => $total,
            'expires_at' => now()->addDays(14),
        ]);
    }
}
