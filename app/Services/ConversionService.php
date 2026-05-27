<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class ConversionService
{
    /**
     * Convierte una cotización aceptada en pedido.
     * Todo dentro de una transacción — todo o nada.
     */
    public function convert(Quotation $quotation): Order
    {
        // 1. Validar que la cotización se puede convertir
        if (!$quotation->isConvertible()) {
            throw new \Exception(
                "La cotización '{$quotation->folio}' no se puede convertir. 
                Estado actual: {$quotation->status}."
            );
        }

        // 2. Todo dentro de una transacción
        return DB::transaction(function () use ($quotation) {

            // 3. Crear el pedido copiando los datos de la cotización
            $order = Order::create([
                'quotation_id'    => $quotation->id,
                'client_id'       => $quotation->client_id,
                'user_id'         => $quotation->user_id,
                'status'          => 'activo',
                'subtotal'        => $quotation->subtotal,
                'discount_global' => $quotation->discount_global,
                'tax'             => $quotation->tax,
                'total'           => $quotation->total,
            ]);

            // 4. Copiar cada línea con el precio congelado
            // OJO: se copia el precio de la LÍNEA, no del catálogo
            foreach ($quotation->items as $item) {
                $order->items()->create([
                    'product_id'    => $item->product_id,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'line_discount' => $item->line_discount,
                    'subtotal'      => $item->subtotal,
                ]);
            }

            // 5. Marcar la cotización como convertida
            $quotation->update(['status' => 'convertida']);

            return $order;
        });
    }
}