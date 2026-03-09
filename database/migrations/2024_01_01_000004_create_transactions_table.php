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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->nullable()->constrained('votes')->onDelete('set null');
            $table->foreignId('don_id')->nullable()->constrained('dons')->onDelete('set null');
            $table->string('moneroo_id', 150)->nullable()->index();      // ID transaction Moneroo
            $table->string('checkout_url', 500)->nullable();              // URL de paiement Moneroo
            $table->integer('montant');                                    // XOF sans décimales
            $table->string('statut', 30)->default('en_attente');          // en_attente | reussi | echoue | annule
            $table->string('type', 20)->default('vote');                  // vote | don
            $table->string('telephone', 20)->nullable();                  // Numéro du payeur
            $table->json('metadata')->nullable();                         // Métadonnées envoyées à Moneroo
            $table->json('response_data')->nullable();                    // Réponse brute webhook Moneroo
            $table->timestamp('processed_at')->nullable();                // Date de traitement final
            $table->timestamps();

            $table->index('statut');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
