<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidat;

class CandidatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider la table d'abord
        Candidat::truncate();

        $candidats = [
            // Filière BTP - Bâtiment et Travaux Publics
            [
                'nom' => 'HOUNKOU',
                'prenom' => 'Jobed',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/hounkou_jobed.jpeg',
                'description' => 'Élève en 1ère F4 au LTP Bopa, passionné par le génie civil et la construction.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BOSSOU',
                'prenom' => 'David',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/bossou_david.jpeg',
                'description' => 'Élève en BTP 2 au LTP Bopa, spécialisé en dessin technique et construction.',
                'total_votes' => 0,
            ],

            // Filière MMV - Menuiserie Métallique et Vitrerie
            [
                'nom' => 'TOMETIN',
                'prenom' => 'Parfaite',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/tometin_parfaite.jpeg',
                'description' => 'Élève en MMV 2 au LTP Bopa, passionnée par la couture et la mode.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'MOUZOUN',
                'prenom' => 'Meshack',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/mouzoun_meshack.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, créatif et talentueux dans son domaine.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'NANNI',
                'prenom' => 'Larissa',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/nanni_larissa.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, passionnée par la création textile.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'DOHOU',
                'prenom' => 'Amos',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/dohou_amos.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, engagé et déterminé dans son parcours.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'TODHEFO',
                'prenom' => 'Vianey',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/todhefo_vianey.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, dynamique et passionné par son métier.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'HOUNZAVI',
                'prenom' => 'Déborah',
                'filiere' => 'MMV',
                'photo_url' => '/uploads/candidats/hounzavi_deborah.jpeg',
                'description' => 'Élève en MMV 3 au LTP Bopa, passionnée par la couture et le stylisme.',
                'total_votes' => 0,
            ],

            // Filière PM - Production Multimédia
            [
                'nom' => 'GNONLONFOUN',
                'prenom' => 'Eloide',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/gnonlonfoun_eloide.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, passionnée par la photographie et le multimédia.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BOSSA',
                'prenom' => 'Phidias',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/bossa_phidias.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, curieux et engagé dans son apprentissage.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'AKPENAN',
                'prenom' => 'Jean',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/akpenan_jean.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, déterminé à exceller dans son domaine.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'ADANKPO',
                'prenom' => 'Andy',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/adankpo_andy.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, jovial et passionné par son métier.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'KOKOUN',
                'prenom' => 'Carine',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/kokoun_carine.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, talentueuse et ambitieuse.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BABAGBETO',
                'prenom' => 'Gloria',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/babagbeto_gloria.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, souriante et déterminée.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BOCCO',
                'prenom' => 'Francklin',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/bocco_francklin.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, passionné par la photographie et le multimédia.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BOCCO',
                'prenom' => 'Winner Cyr',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/bocco_winner_cyr.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, créatif et ambitieux.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'IBRAHIMA',
                'prenom' => 'Mouhoussinou',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/ibrahima_mouhoussinou.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, sérieux et engagé dans ses études.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'KAKANAKOU',
                'prenom' => 'Jules',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/kakanakou_jules.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, dynamique et passionné.',
                'total_votes' => 0,
            ],

            // Filière DWM - Développement Web et Mobile
            [
                'nom' => 'HOUNYO',
                'prenom' => 'Parfait',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/hounyo_parfait.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, passionné par le développement web.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'DALMEIDA',
                'prenom' => 'Chris',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/dalmeida_chris.jpeg',
                'description' => 'Élève en DWM 2 au LTP Bopa, futur développeur talentueux.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'TCHIMON',
                'prenom' => 'Fadel',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/tchimon_fadel.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, passionné par la programmation.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'AKOTANGNI',
                'prenom' => 'Roméo',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/akotegnin_romeo.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, passionné par le développement web et mobile.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'DAGUE',
                'prenom' => 'Victorin',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/dague_victorin.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, engagé et déterminé dans le numérique.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'FANOUDAN',
                'prenom' => 'Grâce',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/fanoudan_grace.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, passionnée par la technologie et le web.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'AHOLOU',
                'prenom' => 'Fleuri',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/aholou_fleuri.jpeg',
                'description' => 'Élève en DWM 3 au LTP Bopa, créatif et motivé dans le développement.',
                'total_votes' => 0,
            ],

            // Nouveaux candidats — EA (Électricité Automobile)
            [
                'nom' => 'AKAKPO',
                'prenom' => 'Janvier',
                'filiere' => 'EA',
                'photo_url' => '/uploads/candidats/akakpo_janvier.jpeg',
                'description' => 'Élève en TEA3 au LTP Bopa, passionné par l\'électricité automobile.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'NOUDEHOU',
                'prenom' => 'Roger',
                'filiere' => 'EA',
                'photo_url' => '/uploads/candidats/noudehou_roger.jpeg',
                'description' => 'Élève en TEA3 au LTP Bopa, curieux et déterminé dans son apprentissage.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'AZONYITO',
                'prenom' => 'Romaric',
                'filiere' => 'EA',
                'photo_url' => '/uploads/candidats/azonyito_romaric.jpeg',
                'description' => 'Élève en TEA3 au LTP Bopa, engagé et passionné par son métier.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'ADOUKONOU',
                'prenom' => 'Alexandre',
                'filiere' => 'EA',
                'photo_url' => '/uploads/candidats/adoukonou_alexandre.jpeg',
                'description' => 'Élève en TEA3 au LTP Bopa, ambitieux et travailleur.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'SOSSOU',
                'prenom' => 'Seth',
                'filiere' => 'EA',
                'photo_url' => '/uploads/candidats/sossou_seth.jpeg',
                'description' => 'Élève en TEA3 au LTP Bopa, souriant et déterminé à exceller.',
                'total_votes' => 0,
            ],

            // Nouveaux candidats — DWM
            [
                'nom' => 'ASSEMOU',
                'prenom' => 'Carlos',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/assemou_carlos.jpeg',
                'description' => 'Élève en DWM2 au LTP Bopa, passionné par le développement web et mobile.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'TABOUE',
                'prenom' => 'Charité',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/taboue_charite.jpeg',
                'description' => 'Élève en DWM2 au LTP Bopa, talentueuse et déterminée.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'AMETEPE',
                'prenom' => 'Pascal',
                'filiere' => 'DWM',
                'photo_url' => '/uploads/candidats/ametepe_pascal.jpeg',
                'description' => 'Élève en DWM2 au LTP Bopa, créatif et motivé.',
                'total_votes' => 0,
            ],

            // Nouveaux candidats — BTP
            [
                'nom' => 'BALLO',
                'prenom' => 'Simon Pierre',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/ballo_simon_pierre.jpeg',
                'description' => 'Élève en BTP3 au LTP Bopa, passionné par le dessin technique et la construction.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'DANSOU',
                'prenom' => 'Emile',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/dansou_emile.jpeg',
                'description' => 'Élève en BTP3 au LTP Bopa, rigoureux et appliqué dans son travail.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'HEGBONDJI',
                'prenom' => 'Adrien',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/hegbondji_adrien.jpeg',
                'description' => 'Élève en 2nde F4 au LTP Bopa, motivé et passionné par le génie civil.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'KPADJI',
                'prenom' => 'Israel',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/kpadji_israel.jpeg',
                'description' => 'Élève en BTP3 au LTP Bopa, sérieux et déterminé.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'ASSOGBA',
                'prenom' => 'Yadariane',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/assogba_yadariane.jpeg',
                'description' => 'Élève en 1ère F4 au LTP Bopa, appliquée et ambitieuse.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'BILOA',
                'prenom' => 'Sergil',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/biloa_sergil.jpeg',
                'description' => 'Élève en 1ère F4 au LTP Bopa, déterminé et passionné par la construction.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'HOUNDJI',
                'prenom' => 'Rayane',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/houndji_rayane.jpeg',
                'description' => 'Élève en BTP3 au LTP Bopa, sérieux et engagé dans ses études.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'GBAGUIDI',
                'prenom' => 'Marilyn',
                'filiere' => 'BTP',
                'photo_url' => '/uploads/candidats/gbaguidi_marilyn.jpeg',
                'description' => 'Élève en 1ère F4 au LTP Bopa, appliqué et travailleur.',
                'total_votes' => 0,
            ],

            // Nouveaux candidats — PM (Producteur Multimédia)
            [
                'nom' => 'DOSSOU',
                'prenom' => 'Landry',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/dossou_landry.jpeg',
                'description' => 'Élève en PM2 au LTP Bopa, passionné par la photographie et le multimédia.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'DJRELEBAHOUE',
                'prenom' => 'Exaucé',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/djrelebahoue_exauce.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, souriant et créatif.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'KOKOUN',
                'prenom' => 'Synthia',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/kokoun_synthia.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, créative et ambitieuse.',
                'total_votes' => 0,
            ],
            [
                'nom' => 'CAMARA',
                'prenom' => 'Marc',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/camara_marc.jpeg',
                'description' => 'Élève en PM2 au LTP Bopa, passionné par la photographie et la réalisation.',
                'total_votes' => 0,
            ],
        ];

        foreach ($candidats as $candidat) {
            Candidat::create($candidat);
        }

        $this->command->info('✅ ' . count($candidats) . ' candidats créés avec succès !');
    }
}
