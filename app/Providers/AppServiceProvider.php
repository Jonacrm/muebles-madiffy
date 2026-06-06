<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $canViewAndCreateSalesResources = fn (User $user): bool => in_array($user->role, ['vendedor', 'admin'], true);
        $canManageAll = fn (User $user): bool => $user->role === 'admin';

        $ownsQuotation = fn (User $user, Quotation $quotation): bool => $canManageAll($user) || $quotation->user_id === $user->id;
        $ownsOrder = fn (User $user, Order $order): bool => $canManageAll($user) || $order->user_id === $user->id;

        // ── Clientes ──────────────────────────────────────────────
        Gate::define('ver-clientes', $canViewAndCreateSalesResources);
        Gate::define('crear-cliente', $canViewAndCreateSalesResources);
        Gate::define('editar-cliente', $canViewAndCreateSalesResources);
        Gate::define('eliminar-cliente', $canManageAll);

        // ── Catálogo ──────────────────────────────────────────────
        Gate::define('ver-catalogo', $canViewAndCreateSalesResources);
        Gate::define('gestionar-catalogo', $canManageAll);

        // ── Cotizaciones ──────────────────────────────────────────
        Gate::define('ver-cotizaciones', $canViewAndCreateSalesResources);
        Gate::define('ver-todas-cotizaciones', $canManageAll);
        Gate::define('crear-cotizacion', $canViewAndCreateSalesResources);
        Gate::define('ver-cotizacion', $ownsQuotation);
        Gate::define('editar-cotizacion', $ownsQuotation);
        Gate::define('cambiar-estado-cotizacion', $ownsQuotation);
        Gate::define('convertir-cotizacion', $ownsQuotation);
        Gate::define('eliminar-cotizacion', $canManageAll);

        // ── Pedidos ───────────────────────────────────────────────
        Gate::define('ver-pedidos', $canViewAndCreateSalesResources);
        Gate::define('ver-todos-pedidos', $canManageAll);
        Gate::define('ver-pedido', $ownsOrder);
        Gate::define('gestionar-pedidos', $canManageAll);
    }
}
