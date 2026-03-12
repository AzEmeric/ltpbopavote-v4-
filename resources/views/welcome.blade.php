@extends('layouts.app')

@section('title', 'Awards 2026 — LTP Bopa')

@section('content')

    <!-- Sticky Nav -->
    <nav class="sticky-nav" id="stickyNav">
        <div class="container sticky-nav-inner">
            <a href="#" class="sticky-logo" onclick="window.scrollTo({top:0,behavior:'smooth'});return false">
                <div class="sticky-logo-icon"><i class="fas fa-trophy"></i></div>
                <div class="logo-text">
                    <span class="logo-name">LTP Bopa</span>
                    <span class="logo-sub">Awards 2026</span>
                </div>
            </a>
            <div class="sticky-right">
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
                <a href="/mes-votes" class="mobile-menu-link">
                    <i class="fas fa-search"></i> Mes votes
                </a>
                <a href="/don" class="mobile-menu-link">
                    <i class="fas fa-hand-holding-heart"></i> Faire un don
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="hero">
        <div class="hero-bg">
            <div class="hero-orb hero-orb-1"></div>
            <div class="hero-orb hero-orb-2"></div>
            <div class="hero-orb hero-orb-3"></div>
            <div class="hero-noise"></div>
            <div class="hero-gradient-line"></div>
        </div>
        <div class="container hero-container">
            <div class="hero-top">
                <a href="#" class="logo">
                    <div class="logo-icon"><i class="fas fa-trophy"></i></div>
                    <div class="logo-text">
                        <span class="logo-name">LTP Bopa</span>
                        <span class="logo-sub">Awards 2026</span>
                    </div>
                </a>
                <div class="hero-top-right">
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
                    <a href="/mes-votes" class="mobile-menu-link">
                        <i class="fas fa-search"></i> Mes votes
                    </a>
                    <a href="/don" class="mobile-menu-link">
                        <i class="fas fa-hand-holding-heart"></i> Faire un don
                    </a>
                </div>
            </div>

            <div class="hero-center">
                <div class="hero-eyebrow">
                    <span class="eyebrow-line"></span>
                    Cérémonie de Distinction &middot; Édition Annuelle
                    <span class="eyebrow-line"></span>
                </div>
                <h1 class="hero-title">
                    <span class="hero-title-awards">AWARDS</span>
                    <span class="hero-title-school">LTP &middot; BOPA</span>
                    <span class="hero-title-sub">Lycée Technique & Professionnel de Bopa</span>
                    <span class="hero-title-year">&middot; 2026 &middot;</span>
                </h1>
                <p class="hero-subtitle">
                    Concours annuel pour distinguer les meilleurs élèves<br class="hide-mobile"> du Lycée Technique et Professionnel de Bopa. Votez et soutenez vos favoris.
                </p>
                <div class="hero-actions">
                    <button class="btn-hero" onclick="scrollToFilieres()">
                        <i class="fas fa-heart"></i>
                        <span>Voter pour un candidat</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <a href="/don" class="btn-hero-outline">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Faire un don</span>
                    </a>
                    <div class="hero-price-tag">
                        <i class="fas fa-coins"></i>
                        {{ config('concours.vote_price') }} FCFA / vote
                    </div>
                </div>
            </div>

            <div class="hero-info-bar">
                <div class="hero-info-item">
                    Edition <strong>2026</strong>
                </div>
                <div class="hero-info-divider"></div>
                <div class="hero-info-item">
                    Organisateur <strong>LTP&middot;BOPA</strong>
                </div>
                <div class="hero-info-divider"></div>
                <div class="hero-info-item">
                    Filières <strong>4</strong>
                </div>
                <div class="hero-info-divider"></div>
                <div class="hero-info-item">
                    Participants <strong>Tous niveaux</strong>
                </div>
            </div>

        </div>
        <div class="hero-scroll-indicator" onclick="scrollToFilieres()">
            <div class="scroll-mouse">
                <div class="scroll-wheel"></div>
            </div>
        </div>
    </header>

    <!-- Separator -->
    <div class="gold-separator">&#9670;</div>

    <!-- Filieres -->
    <section class="section filieres-section" id="filieres">
        <div class="container">
            <div class="section-header reveal">
                <span class="badge-label"><i class="fas fa-layer-group"></i> Filières</span>
                <h2 class="section-title">&mdash; Filières en Compétition &mdash;</h2>
                <p class="section-desc">Sélectionnez une filière pour découvrir les candidats et voter pour votre favori</p>
            </div>

            <div class="filieres-grid">
                @php
                    $filieres = [
                        ['code' => 'DWM', 'name' => 'Développement Web & Mobile', 'icon' => 'fa-code',          'desc' => 'Sites web, applications mobiles et solutions digitales'],
                        ['code' => 'PM',  'name' => 'Producteur Multimédia',       'icon' => 'fa-film',          'desc' => 'Production audiovisuelle, montage et création de contenu'],
                        ['code' => 'MMV', 'name' => 'Mode & Vêtement',             'icon' => 'fa-scissors',      'desc' => 'Stylisme, couture et design de mode'],
                        ['code' => 'BTP', 'name' => 'Bâtiment & Travaux Publics',  'icon' => 'fa-helmet-safety', 'desc' => 'Construction, génie civil et aménagement'],
                    ];
                @endphp

                @foreach($filieres as $index => $f)
                <div class="filiere-card reveal" onclick="showFiliere('{{ $f['code'] }}')" data-filiere="{{ $f['code'] }}" style="animation-delay: {{ $index * 0.08 }}s">
                    <div class="filiere-card-glow"></div>
                    <div class="filiere-icon-wrap">
                        <i class="fas {{ $f['icon'] }}"></i>
                    </div>
                    <div class="filiere-info">
                        <h3 class="filiere-code">{{ $f['code'] }}</h3>
                        <p class="filiere-name">{{ $f['name'] }}</p>
                        <p class="filiere-desc">{{ $f['desc'] }}</p>
                    </div>
                    <div class="filiere-bottom">
                        <span class="filiere-count"><span id="count-{{ $f['code'] }}">0</span> candidats</span>
                        <span class="filiere-arrow"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Trust -->
            <div class="trust-bar reveal">
                <div class="trust-item">
                    <div class="trust-icon"><i class="fas fa-mobile-screen-button"></i></div>
                    <div>
                        <strong>Mobile Money</strong>
                        <span>MTN & Moov - {{ config('concours.vote_price') }} FCFA/vote</span>
                    </div>
                </div>
                <div class="trust-item">
                    <div class="trust-icon"><i class="fas fa-shield-halved"></i></div>
                    <div>
                        <strong>100% Sécurisé</strong>
                        <span>Paiement chiffré et protégé</span>
                    </div>
                </div>
                <div class="trust-item">
                    <div class="trust-icon"><i class="fas fa-bolt"></i></div>
                    <div>
                        <strong>Vote instantané</strong>
                        <span>Confirmation en temps réel</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Candidats -->
    <section class="section candidates-section" id="candidats" style="display: none;">
        <div class="container">
            <div class="section-header">
                <button class="btn-back" onclick="hideFiliere()">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour aux filières</span>
                </button>
                <span class="badge-label" id="filiereBadge">Filière</span>
                <h2 class="section-title" id="filiereTitle">Candidats</h2>
                <p class="section-desc" id="filiereDesc">Cliquez sur un candidat pour voter</p>
            </div>

            <div id="loadingCandidates" class="loading-state">
                <div class="loader-ring">
                    <div></div><div></div><div></div><div></div>
                </div>
                <p>Chargement des candidats...</p>
            </div>

            <div id="candidatesGrid" class="candidates-grid"></div>

            <div id="noCandidates" class="empty-state" style="display: none;">
                <div class="empty-icon"><i class="fas fa-user-slash"></i></div>
                <h3>Aucun candidat</h3>
                <p>Aucun candidat inscrit dans cette filière pour le moment.</p>
                <button class="btn-back" onclick="hideFiliere()">
                    <i class="fas fa-arrow-left"></i> Retour aux filières
                </button>
            </div>
        </div>
    </section>

    <!-- Vote Modal -->
    <div class="modal-overlay" id="voteModalOverlay" onclick="handleOverlayClick(event)">
        <div class="modal-panel" id="voteModal">
            <div class="modal-head">
                <div class="modal-head-info">
                    <img id="modalCandidatPhoto" src="" alt="" class="modal-avatar">
                    <div>
                        <h3 id="modalCandidatNom" class="modal-candidate-name"></h3>
                        <span id="modalCandidatFiliere" class="modal-candidate-filiere"></span>
                    </div>
                </div>
                <button class="modal-close" onclick="closeVoteModal()" aria-label="Fermer">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <!-- Section 1 : Nombre de votes -->
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

                <!-- Resume compact -->
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
                        <div class="footer-logo-icon"><i class="fas fa-trophy"></i></div>
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
                <p>&copy; 2026 Lycée Technique et Professionnel de Bopa. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

@endsection
