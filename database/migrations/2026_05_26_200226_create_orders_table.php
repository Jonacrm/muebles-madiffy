<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['activo', 'entregado', 'cancelado'])->default('activo');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_global', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
