<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PedidoVencimiento
{
    public function vencerExpirados(): int
    {
        $pedidos = DB::transaction(function (): Collection {
            $pedidos = Order::with('items')
                ->where('status', 'pendiente')
                ->whereDate('expires_at', '<', today())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($pedidos as $pedido) {
                $this->devolverStock($pedido);
                $pedido->update(['status' => 'vencido']);
            }

            return $pedidos;
        });

        return $pedidos->count();
    }

    private function devolverStock(Order $pedido): void
    {
        $pedido->items
            ->groupBy('product_id')
            ->sortKeys()
            ->each(function ($items, int $productId): void {
                Product::whereKey($productId)->increment('stock', (int) $items->sum('quantity'));
            });
    }
}
