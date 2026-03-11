<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Candidats
Route::prefix('candidats')->group(function () {
    Route::get('/', [CandidatController::class, 'index'])->name('api.candidats.index');
    Route::get('/filiere/{filiere}', [CandidatController::class, 'parFiliere'])->name('api.candidats.filiere');
    Route::get('/statistiques', [CandidatController::class, 'statistiques'])->name('api.candidats.statistiques');
    Route::get('/{id}', [CandidatController::class, 'show'])->name('api.candidats.show');
});

// Votes
Route::prefix('votes')->group(function () {
    Route::post('/', [VoteController::class, 'store'])->name('api.votes.store');
    Route::get('/{id}', [VoteController::class, 'show'])->name('api.votes.show');
    Route::get('/candidat/{candidatId}', [VoteController::class, 'parCandidat'])->name('api.votes.candidat');
    Route::get('/statistiques/all', [VoteController::class, 'statistiques'])->name('api.votes.statistiques');
});

// Paiement Moneroo (redirection checkout)
Route::prefix('payment')->group(function () {
    // Initier un paiement (vote)
    Route::post('/vote', [PaymentController::class, 'initierVote'])->name('api.payment.vote');

    // Initier un paiement (don)
    Route::post('/don', [PaymentController::class, 'initierDon'])->name('api.payment.don');

    // Vérifier le statut d'un paiement
    Route::get('/verifier', [PaymentController::class, 'verifierDepot'])->name('api.payment.verifier');

    // Recherche par téléphone
    Route::get('/rechercher', [PaymentController::class, 'rechercher'])->name('api.payment.rechercher');

    // Simulation (dev uniquement)
    Route::get('/simulate', [PaymentController::class, 'simulate'])->name('api.payment.simulate');
});

// Health check
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API fonctionnelle',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

// Diagnostic DB (temporaire)
Route::get('/db-check', function () {
    $results = [];
    try {
        $results['connection'] = DB::connection()->getDatabaseName();
        $results['driver'] = DB::connection()->getDriverName();
        $results['select_test'] = DB::select('SELECT 1 as ok')[0]->ok ?? 'fail';
        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        $results['tables'] = array_map(fn($t) => $t->tablename, $tables);
        $results['cache_driver'] = config('cache.default');
        $results['session_driver'] = config('session.driver');
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
    }
    return response()->json($results);
});

// Ajout candidats (temporaire)
Route::get('/add-candidats', function () {
    try {
        $nouveaux = [
            ['nom' => 'AKOTEGNIN', 'prenom' => 'Roméo', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/akotegnin_romeo.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, passionné par le développement web et mobile.'],
            ['nom' => 'DAGUE', 'prenom' => 'Victorin', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/dague_victorin.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, engagé et déterminé dans le numérique.'],
            ['nom' => 'FANOUDAN', 'prenom' => 'Grâce', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/fanoudan_grace.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, passionnée par la technologie et le web.'],
            ['nom' => 'AHOLOU', 'prenom' => 'Fleuri', 'filiere' => 'DWM', 'photo_url' => '/uploads/candidats/aholou_fleuri.jpeg', 'description' => 'Élève en DWM 3 au LTP Bopa, créatif et motivé dans le développement.'],
        ];
        $ajoutes = [];
        foreach ($nouveaux as $data) {
            $exists = \App\Models\Candidat::where('nom', $data['nom'])->where('prenom', $data['prenom'])->first();
            if ($exists) {
                $ajoutes[] = ['nom' => $data['nom'] . ' ' . $data['prenom'], 'status' => 'existe_deja', 'id' => $exists->id];
                continue;
            }
            $c = \App\Models\Candidat::create(array_merge($data, ['total_votes' => 0]));
            $ajoutes[] = ['nom' => $data['nom'] . ' ' . $data['prenom'], 'status' => 'cree', 'id' => $c->id];
        }
        return response()->json(['success' => true, 'candidats' => $ajoutes]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
