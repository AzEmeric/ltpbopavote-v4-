@extends('layouts.app')

@section('title', 'Faire un don — Awards LTPBOPA')

@section('content')

    <div class="donation-page">
        <div class="container">
            <a href="/" class="donation-back">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>

            <div class="donation-card">
                <div class="donation-content">
                    <div class="donation-header">
                        <div class="donation-badge">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h1 class="donation-title">Faire un don</h1>
                        <p class="donation-desc">Votre don contribue à l'organisation de la journée culturelle du LTP Bopa : spectacles, expositions, compétitions entre filières et mise en valeur du talent de nos apprenants. Chaque contribution, même modeste, fait la différence !</p>
                    </div>

                    <!-- Montant -->
                    <p class="donation-label">Montant</p>
                    <div class="donation-actions">
                        <button class="donation-amount-btn" onclick="setDonation(500)">500</button>
                        <button class="donation-amount-btn" onclick="setDonation(1000)">1 000</button>
                        <button class="donation-amount-btn" onclick="setDonation(2000)">2 000</button>
                        <button class="donation-amount-btn" onclick="setDonation(5000)">5 000</button>
                    </div>

                    <div class="donation-custom">
                        <div class="donation-input-wrap">
                            <input type="number" id="donationAmount" class="donation-input" placeholder="Montant libre" min="100">
                            <span class="donation-currency">FCFA</span>
                        </div>
                    </div>

                    <button class="btn-donate" id="btnDonate" onclick="processDonation()">
                        <i class="fas fa-paper-plane"></i>
                        <span>Confirmer</span>
                    </button>

                    <div class="donation-trust">
                        <div class="donation-trust-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Sécurisé</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile Money</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-lock"></i>
                            <span>Confidentiel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

@endsection
