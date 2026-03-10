<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil (gère aussi le retour Moneroo)
Route::get('/', [PaymentController::class, 'accueil'])->name('home');

// Page de don
Route::get('/don', function () {
    return view('don');
})->name('don');

// Page de suivi des votes
Route::get('/mes-votes', function () {
    return view('mes-votes');
})->name('mes-votes');

// Retour après paiement Moneroo (vérifie le statut puis redirige vers l'accueil)
Route::get('/paiement/retour', [PaymentController::class, 'retourPaiement'])->name('paiement.retour');
