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

            // Filière PM - Plomberie
            [
                'nom' => 'GNONLONFOUN',
                'prenom' => 'Eloide',
                'filiere' => 'PM',
                'photo_url' => '/uploads/candidats/gnonlonfoun_eloide.jpeg',
                'description' => 'Élève en PM1 au LTP Bopa, passionnée par la photographie et la plomberie.',
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
                'description' => 'Élève en PM1 au LTP Bopa, passionné par la photographie et la plomberie.',
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
        ];

        foreach ($candidats as $candidat) {
            Candidat::create($candidat);
        }

        $this->command->info('✅ ' . count($candidats) . ' candidats créés avec succès !');
    }
}
