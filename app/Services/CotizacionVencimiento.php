<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CotizacionVencimiento
{
    public function vencerExpiradas(): int
    {
        $cotizaciones = DB::transaction(function (): Collection {
            $cotizaciones = Quotation::with('items')
                ->whereIn('status', ['enviada', 'aceptada'])
                ->whereDate('expires_at', '<', today())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($cotizaciones as $quotation) {
                $this->devolverStock($quotation);
                $quotation->update(['status' => 'vencida']);
            }

            return $cotizaciones;
        });

        return $cotizaciones->count();
    }

    private function devolverStock(Quotation $quotation): void
    {
        $quotation->items
            ->groupBy('product_id')
            ->sortKeys()
            ->each(function ($items, int $productId): void {
                Product::whereKey($productId)->increment('stock', (int) $items->sum('quantity'));
            });
    }
}
