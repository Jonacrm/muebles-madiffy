<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PedidoVencimiento
{
    public function vencerExpirados(): int
    {
        $pedidos = Order::with('items')
            ->where('status', 'pendiente')
            ->whereDate('expires_at', '<', today())
            ->get();

        DB::transaction(function () use ($pedidos): void {
            foreach ($pedidos as $pedido) {
                $this->devolverStock($pedido);
                $pedido->update(['status' => 'vencido']);
            }
        });

        return $pedidos->count();
    }

    private function devolverStock(Order $pedido): void
    {
        $pedido->items
            ->groupBy('product_id')
            ->each(function ($items, int $productId): void {
                Product::whereKey($productId)->increment('stock', (int) $items->sum('quantity'));
            });
    }
}
