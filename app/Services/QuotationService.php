<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;

class QuotationService
{
    const IVA = 0.16;

    /**
     * Calcula los totales de una cotización y los guarda.
     * Fórmula:
     * subtotal_linea = cantidad × precio_unitario − descuento_linea
     * subtotal       = suma de subtotales de línea
     * base           = subtotal − descuento_global
     * total          = base + IVA(base)
     */
    public function calculateTotals(Quotation $quotation): Quotation
    {
        // 1. Recalcula el subtotal de cada línea
        foreach ($quotation->items as $item) {
            $item->subtotal = ($item->quantity * $item->unit_price) - $item->line_discount;
            $item->save();
        }

        // 2. Suma todos los subtotales de línea
        $subtotal = $quotation->items->sum('subtotal');

        // 3. Aplica el descuento global sobre el subtotal
        $base = $subtotal - $quotation->discount_global;

        // 4. Calcula el IVA sobre la base correcta
        $tax = round($base * self::IVA, 2);

        // 5. Total final
        $total = $base + $tax;

        // 6. Guarda los totales en la cotización
        $quotation->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $total,
        ]);

        return $quotation->fresh();
    }

    /**
     * Agrega una línea a la cotización y recalcula los totales.
     */
    public function addItem(Quotation $quotation, array $data): Quotation
    {
        // Calcula el subtotal de la línea antes de guardarla
        $subtotal = ($data['quantity'] * $data['unit_price']) - ($data['line_discount'] ?? 0);

        QuotationItem::create([
            'quotation_id'  => $quotation->id,
            'product_id'    => $data['product_id'],
            'quantity'      => $data['quantity'],
            'unit_price'    => $data['unit_price'],
            'line_discount' => $data['line_discount'] ?? 0,
            'subtotal'      => $subtotal,
        ]);

        // Después de agregar la línea, recalcula todos los totales
        return $this->calculateTotals($quotation);
    }

    /**
     * Elimina una línea y recalcula los totales.
     */
    public function removeItem(Quotation $quotation, QuotationItem $item): Quotation
    {
        $item->delete();
        return $this->calculateTotals($quotation);
    }

    /**
     * Aplica un descuento global y recalcula.
     */
    public function applyGlobalDiscount(Quotation $quotation, float $discount): Quotation
    {
        $quotation->update(['discount_global' => $discount]);
        return $this->calculateTotals($quotation);
    }

    /**
     * Cambia el estado de la cotización.
     * Valida que la transición sea válida.
     */
    public function changeStatus(Quotation $quotation, string $newStatus): Quotation
    {
        $validTransitions = [
            'borrador'   => ['enviada'],
            'enviada'    => ['aceptada', 'rechazada'],
            'aceptada'   => ['convertida'],
            'rechazada'  => [],
            'convertida' => [],
            'vencida'    => [],
        ];

        $currentStatus = $quotation->status;

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new \Exception(
                "No se puede cambiar de '{$currentStatus}' a '{$newStatus}'."
            );
        }

        $quotation->update(['status' => $newStatus]);
        return $quotation->fresh();
    }

    /**
     * Marca como vencidas todas las cotizaciones cuya fecha
     * de vigencia ya pasó y que no están convertidas ni rechazadas.
     */
    public function markExpired(): int
    {
        return Quotation::where('expires_at', '<', now())
            ->whereNotIn('status', ['convertida', 'rechazada', 'vencida'])
            ->update(['status' => 'vencida']);
    }
}