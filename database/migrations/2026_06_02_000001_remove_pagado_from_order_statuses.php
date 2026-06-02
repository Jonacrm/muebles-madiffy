<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'pagado')->update(['status' => 'pendiente']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pendiente', 'enviado', 'vencido') NOT NULL DEFAULT 'pendiente'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pendiente', 'pagado', 'enviado', 'vencido') NOT NULL DEFAULT 'pendiente'");
        }
    }
};
