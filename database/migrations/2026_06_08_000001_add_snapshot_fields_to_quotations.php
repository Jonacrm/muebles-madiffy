<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotations', 'client_name')) {
            Schema::table('quotations', function (Blueprint $table): void {
                $table->string('client_name')->nullable();
                $table->string('client_email')->nullable();
                $table->string('client_phone')->nullable();
                $table->string('client_rfc')->nullable();
                $table->string('client_address')->nullable();
                $table->string('seller_name')->nullable();
            });
        }

        if (! Schema::hasColumn('quotation_items', 'product_name')) {
            Schema::table('quotation_items', function (Blueprint $table): void {
                $table->string('product_sku')->nullable();
                $table->string('product_name')->nullable();
                $table->string('product_material')->nullable();
                $table->text('product_description')->nullable();
            });
        }

        DB::table('quotations')
            ->leftJoin('clients', 'quotations.client_id', '=', 'clients.id')
            ->leftJoin('users', 'quotations.user_id', '=', 'users.id')
            ->select([
                'quotations.id',
                'clients.name as client_name',
                'clients.email as client_email',
                'clients.phone as client_phone',
                'clients.rfc as client_rfc',
                'clients.address as client_address',
                'users.name as seller_name',
            ])
            ->orderBy('quotations.id')
            ->get()
            ->each(function ($quotation): void {
                DB::table('quotations')->where('id', $quotation->id)->update([
                    'client_name' => $quotation->client_name,
                    'client_email' => $quotation->client_email,
                    'client_phone' => $quotation->client_phone,
                    'client_rfc' => $quotation->client_rfc,
                    'client_address' => $quotation->client_address,
                    'seller_name' => $quotation->seller_name,
                ]);
            });

        DB::table('quotation_items')
            ->leftJoin('products', 'quotation_items.product_id', '=', 'products.id')
            ->select([
                'quotation_items.id',
                'products.sku as product_sku',
                'products.name as product_name',
                'products.material as product_material',
                'products.description as product_description',
            ])
            ->orderBy('quotation_items.id')
            ->get()
            ->each(function ($item): void {
                DB::table('quotation_items')->where('id', $item->id)->update([
                    'product_sku' => $item->product_sku,
                    'product_name' => $item->product_name,
                    'product_material' => $item->product_material,
                    'product_description' => $item->product_description,
                ]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('quotation_items', 'product_name')) {
            Schema::table('quotation_items', function (Blueprint $table): void {
                $table->dropColumn([
                    'product_sku',
                    'product_name',
                    'product_material',
                    'product_description',
                ]);
            });
        }

        if (Schema::hasColumn('quotations', 'client_name')) {
            Schema::table('quotations', function (Blueprint $table): void {
                $table->dropColumn([
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
