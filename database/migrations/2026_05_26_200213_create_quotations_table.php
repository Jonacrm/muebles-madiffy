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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', [
                'borrador',
                'enviada',
                'aceptada',
                'convertida',
                'rechazada',
                'vencida'
            ])->default('borrador');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_global', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
