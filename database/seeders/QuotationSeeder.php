<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationService;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $service = new QuotationService();
        $user    = User::first();

        // Cotización 1 — borrador
        $q1 = Quotation::create([
            'folio'           => 'COT-2026-001',
            'client_id'       => 1,
            'user_id'         => $user->id,
            'status'          => 'borrador',
            'subtotal'        => 0,
            'discount_global' => 0,
            'tax'             => 0,
            'total'           => 0,
            'expires_at'      => now()->addDays(15),
        ]);

        $service->addItem($q1, [
            'product_id'    => 1,
            'quantity'      => 10,
            'unit_price'    => 3500.00,
            'line_discount' => 0,
        ]);

        $service->addItem($q1, [
            'product_id'    => 2,
            'quantity'      => 2,
            'unit_price'    => 8900.00,
            'line_discount' => 500,
        ]);

        // Cotización 2 — aceptada (lista para convertir)
        $q2 = Quotation::create([
            'folio'           => 'COT-2026-002',
            'client_id'       => 2,
            'user_id'         => $user->id,
            'status'          => 'aceptada',
            'subtotal'        => 0,
            'discount_global' => 1000,
            'tax'             => 0,
            'total'           => 0,
            'expires_at'      => now()->addDays(30),
        ]);

        $service->addItem($q2, [
            'product_id'    => 3,
            'quantity'      => 1,
            'unit_price'    => 24500.00,
            'line_discount' => 0,
        ]);

        $service->addItem($q2, [
            'product_id'    => 4,
            'quantity'      => 10,
            'unit_price'    => 1200.00,
            'line_discount' => 0,
        ]);

        $service->calculateTotals($q2);

        // Cotización 3 — vencida
        $q3 = Quotation::create([
            'folio'           => 'COT-2026-003',
            'client_id'       => 3,
            'user_id'         => $user->id,
            'status'          => 'vencida',
            'subtotal'        => 0,
            'discount_global' => 0,
            'tax'             => 0,
            'total'           => 0,
            'expires_at'      => now()->subDays(5),
        ]);

        $service->addItem($q3, [
            'product_id'    => 7,
            'quantity'      => 2,
            'unit_price'    => 12000.00,
            'line_discount' => 0,
        ]);
    }
}