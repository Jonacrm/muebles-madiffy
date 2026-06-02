<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'quotation_folio')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('quotation_folio')->nullable();
                $table->string('client_name')->nullable();
                $table->string('client_email')->nullable();
                $table->string('client_phone')->nullable();
                $table->string('client_rfc')->nullable();
                $table->string('client_address')->nullable();
                $table->string('seller_name')->nullable();
            });
        }

        if (! Schema::hasColumn('order_items', 'product_name')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->string('product_sku')->nullable();
                $table->string('product_name')->nullable();
                $table->string('product_material')->nullable();
                $table->text('product_description')->nullable();
            });
        }

        DB::table('orders')
            ->leftJoin('quotations', 'orders.quotation_id', '=', 'quotations.id')
            ->leftJoin('clients', 'orders.client_id', '=', 'clients.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select([
                'orders.id',
                'quotations.folio as quotation_folio',
                'clients.name as client_name',
                'clients.email as client_email',
                'clients.phone as client_phone',
                'clients.rfc as client_rfc',
                'clients.address as client_address',
                'users.name as seller_name',
            ])
            ->orderBy('orders.id')
            ->get()
            ->each(function ($order): void {
                DB::table('orders')->where('id', $order->id)->update([
                    'quotation_folio' => $order->quotation_folio,
                    'client_name' => $order->client_name,
                    'client_email' => $order->client_email,
                    'client_phone' => $order->client_phone,
                    'client_rfc' => $order->client_rfc,
                    'client_address' => $order->client_address,
                    'seller_name' => $order->seller_name,
                ]);
            });

        DB::table('order_items')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->select([
                'order_items.id',
                'products.sku as product_sku',
                'products.name as product_name',
                'products.material as product_material',
                'products.description as product_description',
            ])
            ->orderBy('order_items.id')
            ->get()
            ->each(function ($item): void {
                DB::table('order_items')->where('id', $item->id)->update([
                    'product_sku' => $item->product_sku,
                    'product_name' => $item->product_name,
                    'product_material' => $item->product_material,
                    'product_description' => $item->product_description,
                ]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'product_name')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->dropColumn([
                    'product_sku',
                    'product_name',
                    'product_material',
                    'product_description',
                ]);
            });
        }

        if (Schema::hasColumn('orders', 'quotation_folio')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn([
                    'quotation_folio',
                    'client_name',
                    'client_email',
                    'client_phone',
                    'client_rfc',
                    'client_address',
                    'seller_name',
                ]);
            });
        }
    }
};
