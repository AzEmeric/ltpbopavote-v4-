<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Page de don
Route::get('/don', function () {
    return view('don');
})->name('don');

// Page de suivi des votes
Route::get('/mes-votes', function () {
    return view('mes-votes');
})->name('mes-votes');

// Page de retour après paiement Moneroo
// Moneroo redirige ici avec ?paymentId=xxx&paymentStatus=xxx
Route::get('/paiement/retour', function (\Illuminate\Http\Request $request) {
    $paymentId = $request->query('paymentId');
    $paymentStatus = $request->query('paymentStatus');

    // Mettre à jour le vote/don si le paiement est confirmé par Moneroo
    if ($paymentId && $paymentStatus === 'success') {
        $transaction = \App\Models\Transaction::where('moneroo_id', $paymentId)->first();

        if ($transaction && $transaction->statut === \App\Models\Transaction::STATUT_EN_ATTENTE) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
                $transaction->update([
                    'statut' => \App\Models\Transaction::STATUT_REUSSI,
                    'processed_at' => now(),
                ]);

                if ($transaction->vote_id) {
                    $vote = \App\Models\Vote::find($transaction->vote_id);
                    if ($vote && $vote->estEnAttente()) {
                        $vote->statut_paiement = \App\Models\Vote::STATUT_REUSSI;
                        $vote->save();
                    }
                }
            });
        }
    }

    return view('paiement-retour');
})->name('paiement.retour');
