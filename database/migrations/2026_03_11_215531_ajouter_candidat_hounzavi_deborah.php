<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        try {
            DB::table('candidats')->insert([
                'nom' => 'HOUNZAVI',
                'prenom' => 'Deborah',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/hounzavi_deborah.jpeg',
                'description' => 'Eleve en MMV 3 au LTP Bopa, passionnee par la couture et le stylisme.',
                'total_votes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Ignore si la candidate existe deja
        }
    }

    public function down(): void
    {
        DB::table('candidats')->where('nom', 'HOUNZAVI')->delete();
    }
};
