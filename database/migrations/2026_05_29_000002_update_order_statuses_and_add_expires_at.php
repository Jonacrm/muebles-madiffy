<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('activo', 'entregado', 'cancelado', 'pendiente', 'pagado', 'enviado', 'vencido') NOT NULL DEFAULT 'pendiente'");
        }

        DB::table('orders')->where('status', 'activo')->update(['status' => 'pendiente']);
        DB::table('orders')->where('status', 'entregado')->update(['status' => 'enviado']);
        DB::table('orders')->where('status', 'cancelado')->update(['status' => 'vencido']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pendiente', 'pagado', 'enviado', 'vencido') NOT NULL DEFAULT 'pendiente'");
        }

        if (! Schema::hasColumn('orders', 'expires_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->date('expires_at')->nullable()->after('total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'expires_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('expires_at');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('activo', 'entregado', 'cancelado', 'pendiente', 'pagado', 'enviado', 'vencido') NOT NULL DEFAULT 'activo'");
        }

        DB::table('orders')->where('status', 'pendiente')->update(['status' => 'activo']);
        DB::table('orders')->where('status', 'enviado')->update(['status' => 'entregado']);
        DB::table('orders')->where('status', 'vencido')->update(['status' => 'cancelado']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('activo', 'entregado', 'cancelado') NOT NULL DEFAULT 'activo'");
        }
    }
};
