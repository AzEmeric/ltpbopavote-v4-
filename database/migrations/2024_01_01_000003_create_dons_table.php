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
        Schema::create('dons', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 100)->unique();         // ID unique généré côté serveur
            $table->string('nom_donateur', 100)->nullable();         // Optionnel (don anonyme possible)
            $table->string('telephone', 20);                         // Numéro Mobile Money
            $table->integer('montant');                               // XOF sans décimales (minimum 100)
            $table->string('statut', 20)->default('en_attente');     // en_attente | reussi | echoue
            $table->string('message', 500)->nullable();              // Message d'encouragement optionnel
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dons');
    }
};
