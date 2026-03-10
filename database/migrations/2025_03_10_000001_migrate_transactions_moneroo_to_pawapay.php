<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration Moneroo → PawaPay
     * - Renomme moneroo_id → deposit_id
     * - Supprime checkout_url
     * - Ajoute operateur
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('moneroo_id', 'deposit_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('checkout_url');
            $table->string('operateur', 10)->nullable()->after('telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('deposit_id', 'moneroo_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('checkout_url', 500)->nullable()->after('moneroo_id');
            $table->dropColumn('operateur');
        });
    }
};
