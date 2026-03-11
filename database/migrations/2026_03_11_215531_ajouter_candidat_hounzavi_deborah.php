<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la candidate n'existe pas déjà
        $existe = DB::table('candidats')
            ->where('nom', 'HOUNZAVI')
            ->where('prenom', 'Déborah')
            ->exists();

        if (!$existe) {
            DB::table('candidats')->insert([
                'nom' => 'HOUNZAVI',
                'prenom' => 'Déborah',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/hounzavi_deborah.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, passionnée par la couture et le stylisme.',
                'total_votes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('candidats')
            ->where('nom', 'HOUNZAVI')
            ->where('prenom', 'Déborah')
            ->delete();
    }
};
