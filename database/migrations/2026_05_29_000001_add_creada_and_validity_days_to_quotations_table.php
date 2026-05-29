<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotations', 'validity_days')) {
            Schema::table('quotations', function (Blueprint $table): void {
                $table->unsignedSmallInteger('validity_days')->default(14)->after('expires_at');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotations MODIFY status ENUM('borrador', 'creada', 'enviada', 'aceptada', 'convertida', 'rechazada', 'vencida') NOT NULL DEFAULT 'borrador'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quotations', 'validity_days')) {
            Schema::table('quotations', function (Blueprint $table): void {
                $table->dropColumn('validity_days');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotations MODIFY status ENUM('borrador', 'enviada', 'aceptada', 'convertida', 'rechazada', 'vencida') NOT NULL DEFAULT 'borrador'");
        }
    }
};
