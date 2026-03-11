<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajouter les 4 nouveaux candidats DWM 3.
     */
    public function up(): void
    {
        $candidats = [
            ['nom' => 'AKOTEGNIN', 'prenom' => 'Roméo', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/akotegnin_romeo.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, passionné par le développement web et mobile.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'DAGUE', 'prenom' => 'Victorin', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/dague_victorin.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, engagé et déterminé dans le numérique.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'FANOUDAN', 'prenom' => 'Grâce', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/fanoudan_grace.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, passionnée par la technologie et le web.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'AHOLOU', 'prenom' => 'Fleuri', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/aholou_fleuri.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, créatif et motivé dans le développement.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($candidats as $candidat) {
            $exists = DB::table('candidats')
                ->where('nom', $candidat['nom'])
                ->where('prenom', $candidat['prenom'])
                ->exists();

            if (!$exists) {
                DB::table('candidats')->insert($candidat);
            }
        }
    }

    /**
     * Supprimer les candidats ajoutés.
     */
    public function down(): void
    {
        DB::table('candidats')->whereIn('nom', ['AKOTEGNIN', 'DAGUE', 'FANOUDAN', 'AHOLOU'])->delete();
    }
};
