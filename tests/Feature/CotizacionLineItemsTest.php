<?php

namespace Tests\Feature;

use App\Livewire\CotizacionLineItems;
use Livewire\Livewire;
use Tests\TestCase;

class CotizacionLineItemsTest extends TestCase
{
    public function test_it_updates_product_data_and_totals_with_livewire(): void
    {
        $component = Livewire::test(CotizacionLineItems::class, [
            'productosIniciales' => [
                [
                    'id' => 1,
                    'sku' => 'MES-001',
                    'name' => 'Mesa',
                    'description' => 'Mesa de comedor',
                    'unit_price' => 1000,
                ],
                [
                    'id' => 2,
                    'sku' => 'SIL-001',
                    'name' => 'Silla',
                    'description' => 'Silla tapizada',
                    'unit_price' => 250,
                ],
            ],
            'lineasIniciales' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'line_discount' => 0,
                ],
            ],
            'descuentoGlobalInicial' => 0,
        ]);

        $component
            ->call('seleccionarProducto', 0, 2)
            ->set('lineas.0.quantity', 3)
            ->set('lineas.0.line_discount', 50)
            ->set('discount_global', 100)
            ->assertSee('Silla tapizada')
            ->assertSee('$700.00')
            ->assertSee('$96.00')
            ->assertSee('$696.00');

        $lineas = $component->get('lineas');

        $this->assertSame(2, $lineas[0]['product_id']);
        $this->assertSame('Silla tapizada', $lineas[0]['descripcion']);
        $this->assertEquals(250.0, $lineas[0]['unit_price']);
    }

    public function test_it_can_add_and_remove_lines(): void
    {
        $component = Livewire::test(CotizacionLineItems::class, [
            'productosIniciales' => [],
            'lineasIniciales' => [],
        ]);

        $this->assertCount(1, $component->get('lineas'));

        $component->call('agregarLinea');

        $this->assertCount(2, $component->get('lineas'));

        $component
            ->call('quitarLinea', 0)
            ->call('quitarLinea', 0);

        $this->assertCount(1, $component->get('lineas'));
    }
}
