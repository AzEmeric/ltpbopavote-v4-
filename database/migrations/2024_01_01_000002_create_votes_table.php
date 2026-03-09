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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_id')->constrained('candidats')->onDelete('cascade');
            $table->integer('nombre_votes')->default(1);
            $table->integer('montant_total');                                    // XOF sans décimales (100 FCFA × nombre_votes)
            $table->string('transaction_id', 100)->unique()->nullable();         // ID unique généré côté serveur
            $table->string('telephone', 20)->nullable();                         // Numéro Mobile Money du votant
            $table->string('statut_paiement', 20)->default('en_attente');        // en_attente | reussi | echoue
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('statut_paiement');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
