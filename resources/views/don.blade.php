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

                    <!-- Montant -->
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

                    <div class="donation-separator"></div>

                    <!-- Paiement Mobile Money -->
                    <p class="donation-label">
                        <i class="fas fa-mobile-alt" style="color: var(--gold-500); margin-right: .25rem;"></i>
                        Paiement Mobile Money
                    </p>

                    <!-- Choix de la passerelle -->
                    <div class="gateway-group" style="margin-bottom: 1rem;">
                        <button class="gateway-btn don-gateway-btn active" onclick="setDonGateway('pawapay')" data-gw="pawapay">
                            <i class="fas fa-mobile-screen-button"></i>
                            PawaPay
                        </button>
                        <button class="gateway-btn don-gateway-btn" onclick="setDonGateway('feexpay')" data-gw="feexpay">
                            <i class="fas fa-credit-card"></i>
                            FeexPay
                        </button>
                    </div>

                    <div class="donation-custom">
                        <div class="phone-input-group">
                            <span class="phone-prefix">+229</span>
                            <input type="tel" class="phone-input" id="donTelephone" placeholder="96 00 00 00" maxlength="12" autocomplete="tel">
                        </div>
                    </div>

                    <div class="operateur-group" id="donOperateurSelector" style="margin-bottom: 1.25rem;">
                        <button class="operateur-btn don-operateur-btn active" onclick="setDonOperateur('MTN')" data-op="MTN">
                            <span class="operateur-dot operateur-dot--mtn"></span>
                            MTN MoMo
                        </button>
                        <button class="operateur-btn don-operateur-btn" onclick="setDonOperateur('MOOV')" data-op="MOOV">
                            <span class="operateur-dot operateur-dot--moov"></span>
                            Moov Money
                        </button>
                    </div>

                    <button class="btn-donate" id="btnDonate" onclick="processDonation()">
                        <i class="fas fa-paper-plane"></i>
                        <span>Confirmer le don</span>
                    </button>

                    <div class="donation-trust">
                        <div class="donation-trust-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement securise</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Prompt USSD</span>
                        </div>
                        <div class="donation-trust-item">
                            <i class="fas fa-lock"></i>
                            <span>100% confidentiel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

@endsection
