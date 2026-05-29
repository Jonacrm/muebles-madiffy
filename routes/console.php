<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cotizaciones:vencer', function (): void {
    $count = app(\App\Services\CotizacionVencimiento::class)->vencerExpiradas();

    $this->info("Cotizaciones vencidas: {$count}");
})->purpose('Marca como vencidas las cotizaciones enviadas o aceptadas con vigencia expirada');

Artisan::command('pedidos:vencer', function (): void {
    $count = app(\App\Services\PedidoVencimiento::class)->vencerExpirados();

    $this->info("Pedidos vencidos: {$count}");
})->purpose('Marca como vencidos los pedidos pendientes con plazo de pago expirado');

Schedule::command('cotizaciones:vencer')->daily();
Schedule::command('pedidos:vencer')->daily();
