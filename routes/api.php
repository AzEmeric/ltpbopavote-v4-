<?php

use Illuminate\Support\Facades\Route;
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

// Paiement PawaPay (STK Push)
Route::prefix('payment')->group(function () {
    // Initier un paiement (vote)
    Route::post('/vote', [PaymentController::class, 'initierVote'])->name('api.payment.vote');

    // Initier un paiement (don)
    Route::post('/don', [PaymentController::class, 'initierDon'])->name('api.payment.don');

    // Webhook PawaPay (notification automatique)
    Route::post('/pawapay/callback', [PaymentController::class, 'webhook'])->name('api.payment.pawapay.callback');

    // Webhook FeexPay (notification automatique)
    Route::post('/feexpay/callback', [PaymentController::class, 'webhookFeexpay'])->name('api.payment.feexpay.callback');

    // Vérifier le statut d'un dépôt
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
