@extends('layouts.app')

@php
    $nomComplet = $candidat->nom_complet;
    $photoUrl = $candidat->photo_url_complete ?: asset('uploads/candidats/default.jpg');
    $profilUrl = route('candidat.profil', $candidat->id);
    $filiereNames = [
        'DWM' => 'Développement Web et Mobile',
        'PM'  => 'Producteur Multimédia',
        'MMV' => 'Métier de la Mode et Vêtement',
        'BTP' => 'Bâtiment et Travaux Publics',
        'TEA' => 'Technicien en Électronique Appliquée',
    ];
    $filiereName = $filiereNames[$candidat->filiere] ?? $candidat->filiere;
    $progress = min(round(($candidat->total_votes / 2000) * 100), 100);
    $shareText = "Votez pour {$nomComplet} ({$candidat->filiere}) au Concours de l'Excellence 2026 — LTP Bopa !";
@endphp

@section('title', "{$nomComplet} — Awards 2026 LTP Bopa")

@push('styles')
<meta property="og:type" content="profile">
<meta property="og:title" content="Votez pour {{ $nomComplet }} — Awards 2026">
<meta property="og:description" content="{{ $shareText }}">
<meta property="og:image" content="{{ $photoUrl }}">
<meta property="og:url" content="{{ $profilUrl }}">
<meta property="og:site_name" content="LTP Bopa Awards 2026">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Votez pour {{ $nomComplet }} — Awards 2026">
<meta name="twitter:description" content="{{ $shareText }}">
<meta name="twitter:image" content="{{ $photoUrl }}">
@endpush

@section('content')

    <!-- Sticky Nav -->
    <nav class="sticky-nav visible" id="stickyNav">
        <div class="container sticky-nav-inner">
            <a href="/" class="sticky-logo">
                <img src="/images/logo-ltp-bopa.png" alt="Logo LTP Bopa" class="sticky-logo-img">
                <div class="logo-text">
                    <span class="logo-name">LTP Bopa</span>
                    <span class="logo-sub">Awards 2026</span>
                </div>
            </a>
            <div class="sticky-right">
                <a href="/" class="chip nav-chip-desktop">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                </a>
                <a href="/mes-votes" class="chip nav-chip-desktop">
                    <i class="fas fa-search"></i>
                    <span>Mes votes</span>
                </a>
                <a href="/don" class="chip chip--cta nav-chip-desktop">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Faire un don</span>
                </a>
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer le thème">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
                <button class="hamburger" onclick="toggleMobileMenu(this)" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
            <div class="mobile-menu">
                <a href="/" class="mobile-menu-link">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="/mes-votes" class="mobile-menu-link">
                    <i class="fas fa-search"></i> Mes votes
                </a>
                <a href="/don" class="mobile-menu-link">
                    <i class="fas fa-hand-holding-heart"></i> Faire un don
                </a>
            </div>
        </div>
    </nav>

    <!-- Profil -->
    <section class="profil-section">
        <div class="container">
            <a href="/" class="profil-back">
                <i class="fas fa-arrow-left"></i>
                Retour à l'accueil
            </a>

            <div class="profil-card">
                <div class="profil-photo-wrap">
                    <img src="{{ $photoUrl }}"
                         alt="{{ $nomComplet }}"
                         class="profil-photo"
                         onerror="this.src='{{ asset('uploads/candidats/default.jpg') }}'">
                    <div class="profil-photo-overlay"></div>
                    <div class="profil-votes-badge">
                        <i class="fas fa-heart"></i>
                        <span>{{ number_format($candidat->total_votes, 0, ',', ' ') }}</span>
                    </div>
                </div>

                <div class="profil-info">
                    <span class="profil-filiere-tag">
                        <i class="fas fa-graduation-cap"></i>
                        {{ $candidat->filiere }} — {{ $filiereName }}
                    </span>
                    <h1 class="profil-name">{{ $nomComplet }}</h1>
                    @if($candidat->description)
                        <p class="profil-desc">{{ $candidat->description }}</p>
                    @endif

                    <div class="profil-stats">
                        <div class="profil-stat">
                            <span class="profil-stat-value">{{ number_format($candidat->total_votes, 0, ',', ' ') }}</span>
                            <span class="profil-stat-label">votes</span>
                        </div>
                        <div class="profil-stat">
                            <span class="profil-stat-value">{{ $statistiques['total_transactions'] ?? 0 }}</span>
                            <span class="profil-stat-label">supporters</span>
                        </div>
                        <div class="profil-stat">
                            <span class="profil-stat-value">{{ $rang }}<span style="font-size:.65em;color:var(--text-secondary)">/{{ $totalDansFiliere }}</span></span>
                            <span class="profil-stat-label">rang {{ $candidat->filiere }}</span>
                        </div>
                    </div>

                    <div class="vote-progress profil-progress">
                        <div class="progress-header">
                            <span>Popularité</span>
                            <span class="count">{{ number_format($candidat->total_votes, 0, ',', ' ') }} vote{{ $candidat->total_votes !== 1 ? 's' : '' }}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <button class="btn-vote profil-btn-vote" onclick="openVoteModalProfil()">
                        <i class="fas fa-heart"></i>
                        <span>Voter pour {{ $candidat->prenom }}</span>
                    </button>

                    <!-- Boutons de partage -->
                    <div class="share-section">
                        <p class="share-label">
                            <i class="fas fa-share-alt"></i>
                            Partagez ce profil
                        </p>
                        <div class="share-buttons">
                            <button class="share-btn share-btn--whatsapp" onclick="shareCandidat('whatsapp')" aria-label="Partager sur WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp</span>
                            </button>
                            <button class="share-btn share-btn--facebook" onclick="shareCandidat('facebook')" aria-label="Partager sur Facebook">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </button>
                            <button class="share-btn share-btn--copy" onclick="copyProfileLink()" aria-label="Copier le lien">
                                <i class="fas fa-link"></i>
                                <span>Copier le lien</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vote Modal -->
    <div class="modal-overlay" id="voteModalOverlay" onclick="handleOverlayClick(event)">
        <div class="modal-panel" id="voteModal">
            <div class="modal-head">
                <div class="modal-head-info">
                    <img id="modalCandidatPhoto" src="{{ $photoUrl }}" alt="{{ $nomComplet }}" class="modal-avatar">
                    <div>
                        <h3 id="modalCandidatNom" class="modal-candidate-name">{{ $nomComplet }}</h3>
                        <span id="modalCandidatFiliere" class="modal-candidate-filiere">{{ $candidat->filiere }}</span>
                    </div>
                </div>
                <button class="modal-close" onclick="closeVoteModal()" aria-label="Fermer">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-section">
                    <label class="input-label">
                        <i class="fas fa-heart modal-section-icon"></i>
                        Nombre de votes
                    </label>
                    <div class="votes-row">
                        <div class="vote-stepper">
                            <button class="stepper-btn" onclick="decrementVotes()" aria-label="Moins">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="stepper-input" id="nombreVotes" value="1" min="1" max="900">
                            <button class="stepper-btn" onclick="incrementVotes()" aria-label="Plus">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="quick-votes">
                            <button class="quick-vote-btn" onclick="setVotes(1)">1</button>
                            <button class="quick-vote-btn" onclick="setVotes(5)">5</button>
                            <button class="quick-vote-btn" onclick="setVotes(10)">10</button>
                            <button class="quick-vote-btn" onclick="setVotes(25)">25</button>
                            <button class="quick-vote-btn" onclick="setVotes(50)">50</button>
                        </div>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="price-summary-left">
                        <i class="fas fa-shield-alt"></i>
                        <span><strong id="summaryVotes">1</strong> vote &middot; Paiement sécurisé</span>
                    </div>
                    <span class="price-summary-total" id="totalAmount">{{ config('concours.vote_price') }} FCFA</span>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn-cancel" onclick="closeVoteModal()">Annuler</button>
                <button class="btn-pay" id="btnPay" onclick="processPayment()">
                    <i class="fas fa-paper-plane"></i>
                    <span>Confirmer</span>
                    <span class="btn-pay-amount" id="btnPayAmount">{{ config('concours.vote_price') }} FCFA</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="/images/logo-ltp-bopa.png" alt="Logo LTP Bopa" class="footer-logo-img">
                        <div>
                            <span class="footer-logo-name">LTP Bopa</span>
                            <span class="footer-logo-sub">Awards 2026</span>
                        </div>
                    </div>
                    <p>Célébrons le talent et l'excellence du Lycée Technique et Professionnel de Bopa, au Bénin.</p>
                </div>
                <div class="footer-links">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> contact@ltpbopa.bj</li>
                        <li><i class="fas fa-phone"></i> (+229) 01 97 19 09 84</li>
                        <li><i class="fas fa-phone"></i> (+229) 01 94 48 80 64</li>
                        <li><i class="fas fa-phone"></i> (+229) 01 97 60 38 66</li>
                        <li><i class="fas fa-location-dot"></i> Bopa, Bénin</li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Suivez-nous</h4>
                    <div class="social-row">
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-link" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 LTPBOPA/DWM. TOUS DROITS RÉSERVÉS.</p>
            </div>
        </div>
    </footer>

@endsection

@push('scripts')
<script>
    const profilCandidat = {
        id: {{ $candidat->id }},
        nom: @json($candidat->nom),
        prenom: @json($candidat->prenom),
        filiere: @json($candidat->filiere),
        photo_url_complete: @json($photoUrl),
        total_votes: {{ $candidat->total_votes }}
    };

    const profilUrl = @json($profilUrl);
    const shareText = @json($shareText);

    function openVoteModalProfil() {
        appState.candidats = [profilCandidat];
        appState.currentCandidat = profilCandidat;
        appState.currentFiliere = profilCandidat.filiere;
        appState.nombreVotes = 1;

        document.getElementById('nombreVotes').value = 1;
        updatePaymentSummary();
        highlightQuickVote(1);

        const btn = document.getElementById('btnPay');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-paper-plane"></i> <span>Confirmer</span> <span class="btn-pay-amount" id="btnPayAmount">${CONFIG.votePrice} FCFA</span>`;
        }

        const overlay = document.getElementById('voteModalOverlay');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function shareCandidat(platform) {
        const url = encodeURIComponent(profilUrl);
        const text = encodeURIComponent(shareText);

        switch (platform) {
            case 'whatsapp':
                window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
                break;
            case 'facebook':
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
                break;
        }
    }

    function copyProfileLink() {
        navigator.clipboard.writeText(profilUrl).then(() => {
            showToast('Lien copié dans le presse-papier !', 'success');
        }).catch(() => {
            const input = document.createElement('input');
            input.value = profilUrl;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            showToast('Lien copié !', 'success');
        });
    }

    window.openVoteModalProfil = openVoteModalProfil;
    window.shareCandidat = shareCandidat;
    window.copyProfileLink = copyProfileLink;
</script>
@endpush
