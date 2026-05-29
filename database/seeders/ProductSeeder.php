<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku'         => 'MUE-001',
                'name'        => 'Silla ejecutiva giratoria',
                'material'    => 'Cuero sintético / Aluminio',
                'description' => 'Silla ergonómica con soporte lumbar.',
                'unit_price'  => 3500.00,
                'stock'       => 20,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-002',
                'name'        => 'Escritorio ejecutivo en L',
                'material'    => 'MDF / Melamina',
                'description' => 'Escritorio en forma de L con cajones.',
                'unit_price'  => 8900.00,
                'stock'       => 8,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-003',
                'name'        => 'Mesa de juntas 10 personas',
                'material'    => 'Madera de caoba',
                'description' => 'Mesa rectangular para sala de juntas.',
                'unit_price'  => 24500.00,
                'stock'       => 3,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-004',
                'name'        => 'Silla de visita tapizada',
                'material'    => 'Tela / Madera',
                'description' => 'Silla fija para área de espera.',
                'unit_price'  => 1200.00,
                'stock'       => 50,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-005',
                'name'        => 'Librero modular 5 niveles',
                'material'    => 'MDF lacado',
                'description' => 'Librero con 5 repisas ajustables.',
                'unit_price'  => 4200.00,
                'stock'       => 12,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-006',
                'name'        => 'Archivero metálico 4 gavetas',
                'material'    => 'Acero',
                'description' => 'Archivero de acero con llave.',
                'unit_price'  => 5800.00,
                'stock'       => 15,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-007',
                'name'        => 'Sofá de espera 3 plazas',
                'material'    => 'Cuero genuino / Madera',
                'description' => 'Sofá para sala de espera o recepción.',
                'unit_price'  => 12000.00,
                'stock'       => 5,
                'active'      => true,
            ],
            [
                'sku'         => 'MUE-008',
                'name'        => 'Estación de trabajo modular',
                'material'    => 'MDF / Aluminio',
                'description' => 'Cubículo individual con panel divisorio.',
                'unit_price'  => 6500.00,
                'stock'       => 18,
                'active'      => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}