@extends('layouts.app')

@section('title', 'Mes votes — Awards LTPBOPA')

@section('content')

    <div class="lookup-page">
        <div class="container">
            <a href="/" class="donation-back">
                <i class="fas fa-arrow-left"></i>
                <span>Retour a l'accueil</span>
            </a>

            <div class="lookup-card">
                <div class="lookup-content">
                    <div class="donation-badge">
                        <i class="fas fa-search"></i>
                        <span>Suivi</span>
                    </div>
                    <h1 class="donation-title">Retrouvez vos votes</h1>
                    <p class="donation-desc">
                        Entrez le numero utilise lors du paiement pour verifier que vos votes ont bien ete comptabilises.
                    </p>

                    <div class="donation-separator"></div>

                    <div class="lookup-form">
                        <p class="donation-label">Numero de telephone</p>
                        <div class="donation-input-wrap donation-input-wrap--full">
                            <span class="donation-phone-prefix">+229</span>
                            <input type="tel" id="lookupPhone" class="donation-input donation-input--phone" placeholder="96 00 00 00" maxlength="11"
                                   onkeydown="if(event.key==='Enter') rechercherVotes()">
                        </div>
                        <button class="btn-donate" id="btnLookup" onclick="rechercherVotes()">
                            <i class="fas fa-search"></i>
                            <span>Rechercher</span>
                        </button>
                    </div>

                    <!-- Loading -->
                    <div id="lookupLoading" class="lookup-loading" style="display: none;">
                        <div class="lookup-spinner"></div>
                        <p>Recherche en cours...</p>
                    </div>

                    <!-- Résultats -->
                    <div id="lookupResults" class="lookup-results" style="display: none;">
                        <div class="donation-separator"></div>

                        <!-- Résumé -->
                        <div id="lookupSummary" class="lookup-summary" style="display: none;"></div>

                        <!-- Votes -->
                        <div id="lookupVotes" style="display: none;">
                            <p class="lookup-section-title">
                                <i class="fas fa-heart"></i>
                                Vos votes
                            </p>
                            <div id="lookupVotesList" class="lookup-list"></div>
                        </div>

                        <!-- Dons -->
                        <div id="lookupDons" style="display: none;">
                            <p class="lookup-section-title">
                                <i class="fas fa-hand-holding-heart"></i>
                                Vos dons
                            </p>
                            <div id="lookupDonsList" class="lookup-list"></div>
                        </div>

                        <!-- Aucun résultat -->
                        <div id="lookupEmpty" class="lookup-empty" style="display: none;">
                            <i class="fas fa-inbox"></i>
                            <p>Aucun vote ou don trouve pour ce numero.</p>
                            <span>Verifiez le numero ou contactez-nous si le probleme persiste.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

@endsection
