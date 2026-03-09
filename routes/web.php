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
Route::get('/paiement/retour', function () {
    return view('paiement-retour');
})->name('paiement.retour');
