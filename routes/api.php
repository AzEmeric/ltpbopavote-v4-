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

// Ajout candidat (temporaire)
Route::get('/add-candidat', function () {
    try {
        $exists = \App\Models\Candidat::where('nom', 'HOUNZAVI')->where('prenom', 'Déborah')->first();
        if ($exists) {
            return response()->json(['success' => true, 'message' => 'Déjà existante', 'id' => $exists->id]);
        }
        $c = \App\Models\Candidat::create([
            'nom' => 'HOUNZAVI',
            'prenom' => 'Déborah',
            'filiere' => 'MMV',
            'photo_url' => '/uploads/candidats/hounzavi_deborah.jpeg',
            'description' => 'Élève en MMV 3 au LTP Bopa, passionnée par la couture et le stylisme.',
            'total_votes' => 0,
        ]);
        return response()->json(['success' => true, 'id' => $c->id]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
