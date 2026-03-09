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
        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('filiere', 10)->index();              // DWM, PM, MMV, BTP, EA
            $table->string('photo_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->integer('total_votes')->default(0)->index();  // Incrémenté via boot hook Vote
            $table->boolean('actif')->default(true)->index();     // Masquer un candidat sans supprimer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
