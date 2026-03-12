<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $candidats = [
            ['nom' => 'AKAKPO', 'prenom' => 'Janvier', 'filiere' => 'EA', 'photo_url' => '/uploads/candidats/akakpo_janvier.jpeg', 'description' => 'Élève en TEA3 au LTP Bopa, passionné par l\'électricité automobile.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'NOUDEHOU', 'prenom' => 'Roger', 'filiere' => 'EA', 'photo_url' => '/uploads/candidats/noudehou_roger.jpeg', 'description' => 'Élève en TEA3 au LTP Bopa, curieux et déterminé dans son apprentissage.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'AZONYITO', 'prenom' => 'Romaric', 'filiere' => 'EA', 'photo_url' => '/uploads/candidats/azonyito_romaric.jpeg', 'description' => 'Élève en TEA3 au LTP Bopa, engagé et passionné par son métier.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'ADOUKONOU', 'prenom' => 'Alexandre', 'filiere' => 'EA', 'photo_url' => '/uploads/candidats/adoukonou_alexandre.jpeg', 'description' => 'Élève en TEA3 au LTP Bopa, ambitieux et travailleur.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'SOSSOU', 'prenom' => 'Seth', 'filiere' => 'EA', 'photo_url' => '/uploads/candidats/sossou_seth.jpeg', 'description' => 'Élève en TEA3 au LTP Bopa, souriant et déterminé à exceller.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'ASSEMOU', 'prenom' => 'Carlos', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/assemou_carlos.jpeg', 'description' => 'Élève en DWM2 au LTP Bopa, passionné par le développement web et mobile.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'TABOUE', 'prenom' => 'Charité', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/taboue_charite.jpeg', 'description' => 'Élève en DWM2 au LTP Bopa, talentueuse et déterminée.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'AMETEPE', 'prenom' => 'Pascal', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/ametepe_pascal.jpeg', 'description' => 'Élève en DWM2 au LTP Bopa, créatif et motivé.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'BALLO', 'prenom' => 'Simon Pierre', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/ballo_simon_pierre.jpeg', 'description' => 'Élève en BTP3 au LTP Bopa, passionné par le dessin technique et la construction.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'DANSOU', 'prenom' => 'Emile', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/dansou_emile.jpeg', 'description' => 'Élève en BTP3 au LTP Bopa, rigoureux et appliqué dans son travail.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'HEGBONDJI', 'prenom' => 'Adrien', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/hegbondji_adrien.jpeg', 'description' => 'Élève en 2nde F4 au LTP Bopa, motivé et passionné par le génie civil.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'KPADJI', 'prenom' => 'Israel', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/kpadji_israel.jpeg', 'description' => 'Élève en BTP3 au LTP Bopa, sérieux et déterminé.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'ASSOGBA', 'prenom' => 'Yadariane', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/assogba_yadariane.jpeg', 'description' => 'Élève en 1ère F4 au LTP Bopa, appliquée et ambitieuse.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'BILOA', 'prenom' => 'Sergil', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/biloa_sergil.jpeg', 'description' => 'Élève en 1ère F4 au LTP Bopa, déterminé et passionné par la construction.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'HOUNDJI', 'prenom' => 'Rayane', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/houndji_rayane.jpeg', 'description' => 'Élève en BTP3 au LTP Bopa, sérieux et engagé dans ses études.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'GBAGUIDI', 'prenom' => 'Marilyn', 'filiere' => 'BTP', 'photo_url' => '/uploads/candidats/gbaguidi_marilyn.jpeg', 'description' => 'Élève en 1ère F4 au LTP Bopa, appliqué et travailleur.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'DOSSOU', 'prenom' => 'Landry', 'filiere' => 'PM', 'photo_url' => '/uploads/candidats/dossou_landry.jpeg', 'description' => 'Élève en PM2 au LTP Bopa, passionné par la photographie et le multimédia.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'DJRELEBAHOUE', 'prenom' => 'Exaucé', 'filiere' => 'PM', 'photo_url' => '/uploads/candidats/djrelebahoue_exauce.jpeg', 'description' => 'Élève en PM1 au LTP Bopa, souriant et créatif.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'KOKOUN', 'prenom' => 'Synthia', 'filiere' => 'PM', 'photo_url' => '/uploads/candidats/kokoun_synthia.jpeg', 'description' => 'Élève en PM1 au LTP Bopa, créative et ambitieuse.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'CAMARA', 'prenom' => 'Marc', 'filiere' => 'PM', 'photo_url' => '/uploads/candidats/camara_marc.jpeg', 'description' => 'Élève en PM2 au LTP Bopa, passionné par la photographie et la réalisation.', 'total_votes' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($candidats as $candidat) {
            $exists = DB::table('candidats')
                ->where('nom', $candidat['nom'])
                ->where('prenom', $candidat['prenom'])
                ->where('filiere', $candidat['filiere'])
                ->exists();

            if (!$exists) {
                DB::table('candidats')->insert($candidat);
            }
        }
    }

    public function down(): void
    {
        $noms = ['AKAKPO', 'NOUDEHOU', 'AZONYITO', 'ADOUKONOU', 'SOSSOU', 'ASSEMOU', 'TABOUE', 'AMETEPE', 'BALLO', 'DANSOU', 'HEGBONDJI', 'KPADJI', 'ASSOGBA', 'BILOA', 'HOUNDJI', 'GBAGUIDI', 'DOSSOU', 'DJRELEBAHOUE', 'KOKOUN', 'CAMARA'];

        DB::table('candidats')->whereIn('nom', $noms)->delete();
    }
};
