@extends('layouts.app')

@section('title', 'Faire un don — Awards LTPBOPA')

@section('content')

    <div class="donation-page">
        <div class="container">
            <a href="/" class="donation-back">
                <i class="fas fa-arrow-left"></i>
                <span>Retour a l'accueil</span>
            </a>

            <div class="donation-card">
                <div class="donation-content">
                    <div class="donation-badge">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Don libre</span>
                    </div>
                    <h1 class="donation-title">Soutenez nos candidats</h1>
                    <p class="donation-desc">
                        Faites un don pour encourager les talents des Awards LTPBOPA. Chaque contribution, meme modeste, fait la difference.
                    </p>

                    <div class="donation-separator"></div>

                    <p class="donation-label">Choisissez un montant</p>
                    <div class="donation-actions">
                        <button class="donation-amount-btn" onclick="setDonation(500)">500 F</button>
                        <button class="donation-amount-btn" onclick="setDonation(1000)">1 000 F</button>
                        <button class="donation-amount-btn" onclick="setDonation(2000)">2 000 F</button>
                        <button class="donation-amount-btn" onclick="setDonation(5000)">5 000 F</button>
                    </div>

                    <p class="donation-label">Ou entrez un montant libre</p>
                    <div class="donation-custom">
                        <div class="donation-input-wrap">
                            <input type="number" id="donationAmount" class="donation-input" placeholder="Ex : 3000" min="100">
                            <span class="donation-currency">FCFA</span>
                        </div>
                    </div>

                    <div class="donation-phone-group">
                        <p class="donation-label">Numero Mobile Money</p>
                        <div class="donation-input-wrap donation-input-wrap--full">
                            <span class="donation-phone-prefix">+229</span>
                            <input type="tel" id="donationPhone" class="donation-input donation-input--phone" placeholder="96 00 00 00" maxlength="11">
                        </div>
                    </div>

                    <button class="btn-donate" id="btnDonate" onclick="processDonation()">
                        <i class="fas fa-heart"></i>
                        <span>Faire un don</span>
                    </button>

                    <div class="donation-trust">
                        <div class="donation-trust-item">
                            <i class="fas fa-lock"></i>
                            <span>Paiement securise</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>MTN & Moov</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>100% confidentiel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

@endsection
