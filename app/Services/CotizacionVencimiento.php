<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class CotizacionVencimiento
{
    public function vencerExpiradas(): int
    {
        $cotizaciones = Quotation::with('items')
            ->whereIn('status', ['enviada', 'aceptada'])
            ->whereDate('expires_at', '<', today())
            ->get();

        DB::transaction(function () use ($cotizaciones): void {
            foreach ($cotizaciones as $quotation) {
                $this->devolverStock($quotation);
                $quotation->update(['status' => 'vencida']);
            }
        });

        return $cotizaciones->count();
    }

    private function devolverStock(Quotation $quotation): void
    {
        $quotation->items
            ->groupBy('product_id')
            ->each(function ($items, int $productId): void {
                Product::whereKey($productId)->increment('stock', (int) $items->sum('quantity'));
            });
    }
}
