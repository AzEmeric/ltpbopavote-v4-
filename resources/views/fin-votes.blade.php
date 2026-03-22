@extends('layouts.app')

@section('title', 'Votes terminés — Awards 2026 LTP Bopa')

@push('styles')
<style>
    /* Header */
    .fin-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: rgba(var(--bg-body-rgb, 11, 17, 32), .85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--border);
        padding: .6rem 0;
    }
    .fin-header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: var(--container-max);
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    .fin-header-logo {
        display: flex;
        align-items: center;
        gap: .6rem;
        text-decoration: none;
    }
    .fin-header-logo-img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
    }
    .fin-header-right {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .fin-page {
        min-height: 100vh;
        padding-top: 4.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 2rem 1rem;
    }

    /* Background animation */
    .fin-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
    }
    .fin-bg-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: .15;
        animation: float-orb 8s ease-in-out infinite;
    }
    .fin-bg-orb-1 {
        width: 500px; height: 500px;
        background: var(--gold-500);
        top: -10%; left: -10%;
        animation-delay: 0s;
    }
    .fin-bg-orb-2 {
        width: 400px; height: 400px;
        background: var(--gold-400);
        bottom: -10%; right: -10%;
        animation-delay: -3s;
    }
    .fin-bg-orb-3 {
        width: 300px; height: 300px;
        background: var(--gold-300);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        animation-delay: -5s;
    }
    @keyframes float-orb {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -20px) scale(1.05); }
        66% { transform: translate(-20px, 15px) scale(.95); }
    }

    /* Confetti container */
    .confetti-container {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        top: -10px;
        opacity: 0;
        animation: confetti-fall linear forwards;
    }
    @keyframes confetti-fall {
        0% { opacity: 1; transform: translateY(0) rotate(0deg); }
        100% { opacity: 0; transform: translateY(100vh) rotate(720deg); }
    }

    /* Main content */
    .fin-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 700px;
    }

    /* Trophy icon */
    .fin-trophy {
        font-size: 5rem;
        color: var(--gold-500);
        margin-bottom: 1.5rem;
        animation: trophy-bounce 2s ease-in-out infinite;
        filter: drop-shadow(0 0 30px rgba(212, 175, 55, .3));
    }
    @keyframes trophy-bounce {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-10px) scale(1.05); }
    }

    /* Title */
    .fin-title {
        font-family: var(--font-display);
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 900;
        color: var(--gold-500);
        line-height: 1.15;
        margin-bottom: 1rem;
        text-shadow: 0 2px 20px rgba(212, 175, 55, .2);
    }

    /* Subtitle */
    .fin-subtitle {
        font-family: var(--font-display);
        font-size: clamp(1.1rem, 2.5vw, 1.5rem);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: .5rem;
    }

    /* Message */
    .fin-message {
        font-size: 1.1rem;
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: 2rem;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Divider */
    .fin-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--gold-500), transparent);
        margin: 0 auto 2rem;
        border-radius: 2px;
    }

    /* Merci section — timeline style */
    .fin-merci-section {
        margin-bottom: 2.5rem;
        position: relative;
        padding-left: 2rem;
        text-align: left;
    }
    .fin-merci-section::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: linear-gradient(180deg, var(--gold-500), var(--gold-500) 50%, var(--green));
        border-radius: 2px;
    }
    .fin-merci-item {
        position: relative;
        padding: 0 0 1.75rem;
    }
    .fin-merci-item:last-child {
        padding-bottom: 0;
    }
    .fin-merci-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 8px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--gold-500);
        border: 2px solid var(--bg-body);
        transform: translateX(calc(-50% + 1px));
        z-index: 1;
    }
    .fin-merci-item:last-child::before {
        background: var(--green);
    }
    .fin-merci-item h4 {
        font-family: var(--font-display);
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 .35rem;
        line-height: 1.3;
    }
    .fin-merci-item p {
        font-size: .95rem;
        color: var(--text-secondary);
        line-height: 1.65;
        margin: 0;
    }

    /* CTA Buttons */
    .fin-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }
    .fin-btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .85rem 1.75rem;
        border-radius: var(--radius-full);
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all .3s var(--ease);
        border: none;
        cursor: pointer;
    }
    .fin-btn-primary {
        background: linear-gradient(135deg, var(--gold-500), var(--gold-600));
        color: #000;
        box-shadow: 0 4px 15px rgba(212, 175, 55, .3);
    }
    .fin-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(212, 175, 55, .4);
    }
    .fin-btn-outline {
        background: transparent;
        color: var(--gold-500);
        border: 2px solid rgba(212, 175, 55, .3);
    }
    .fin-btn-outline:hover {
        background: rgba(212, 175, 55, .08);
        border-color: var(--gold-500);
        transform: translateY(-2px);
    }

    /* Logo bottom */
    .fin-logo {
        display: flex;
        align-items: center;
        gap: .75rem;
        justify-content: center;
        margin-top: 1rem;
        opacity: .6;
        transition: opacity .3s;
    }
    .fin-logo:hover {
        opacity: 1;
    }
    .fin-logo img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    .fin-logo-text {
        text-align: left;
    }
    .fin-logo-name {
        font-weight: 700;
        color: var(--text-primary);
        font-size: .9rem;
        display: block;
    }
    .fin-logo-sub {
        font-size: .75rem;
        color: var(--text-secondary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .fin-page {
            padding: 4.5rem 1rem 2rem;
        }
        .fin-trophy {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        .fin-message {
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        .fin-merci-section {
            padding-left: 1.5rem;
            margin-bottom: 2rem;
        }
        .fin-merci-item::before {
            left: -1.5rem;
        }
        .fin-merci-item h4 {
            font-size: .95rem;
        }
        .fin-merci-item p {
            font-size: .88rem;
        }
        .fin-actions {
            flex-direction: column;
            align-items: center;
        }
        .fin-btn {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
    }
    @media (max-width: 576px) {
        .fin-header-inner {
            padding: 0 1rem;
        }
        .fin-header-right .chip span {
            display: none;
        }
        .fin-content {
            max-width: 100%;
        }
        .fin-trophy {
            font-size: 3rem;
        }
        .fin-subtitle {
            font-size: 1rem;
        }
        .fin-divider {
            margin-bottom: 1.5rem;
        }
    }
</style>
@endpush

@section('content')

    <!-- Header -->
    <nav class="fin-header">
        <div class="container fin-header-inner">
            <a href="/" class="fin-header-logo">
                <img src="/images/logo-ltp-bopa.png" alt="Logo LTP Bopa" class="fin-header-logo-img">
                <div class="logo-text">
                    <span class="logo-name">LTP Bopa</span>
                    <span class="logo-sub">Awards 2026</span>
                </div>
            </a>
            <div class="fin-header-right">
                <a href="/mes-votes" class="chip">
                    <i class="fas fa-search"></i>
                    <span>Mes votes</span>
                </a>
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer le thème">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="fin-page">
        <!-- Background -->
        <div class="fin-bg">
            <div class="fin-bg-orb fin-bg-orb-1"></div>
            <div class="fin-bg-orb fin-bg-orb-2"></div>
            <div class="fin-bg-orb fin-bg-orb-3"></div>
        </div>

        <!-- Confetti -->
        <div class="confetti-container" id="confettiContainer"></div>

        <!-- Content -->
        <div class="fin-content">
            <div class="fin-trophy">
                <i class="fas fa-trophy"></i>
            </div>

            <h1 class="fin-title">Les votes sont terminés !</h1>
            <p class="fin-subtitle">Concours de l'Excellence &mdash; Awards 2026</p>

            <div class="fin-divider"></div>

            <p class="fin-message">
                La phase de votes du Concours de l'Excellence 2026 du Lycée Technique et Professionnel de Bopa est désormais clôturée.
            </p>

            <!-- Merci -->
            <div class="fin-merci-section">
                <div class="fin-merci-item">
                    <h4>Merci pour votre soutien</h4>
                    <p>Un immense merci à chacun d'entre vous pour votre participation tout au long de ce concours. Grâce à vous, les candidats ont pu montrer leur talent et leur détermination.</p>
                </div>
                <div class="fin-merci-item">
                    <h4>Rendez-vous pour les résultats</h4>
                    <p>Les résultats seront dévoilés lors de la présentation devant les jurys. Restez connectés pour ne rien manquer !</p>
                </div>
                <div class="fin-merci-item">
                    <h4>La fierté du LTP Bopa</h4>
                    <p>Votre engagement fait la fierté du Lycée Technique et Professionnel de Bopa. Ensemble, célébrons l'excellence !</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="fin-actions">
                <a href="/mes-votes" class="fin-btn fin-btn-primary">
                    <i class="fas fa-search"></i>
                    Retrouver mes votes
                </a>
            </div>

            <!-- Logo -->
            <div class="fin-logo">
                <img src="/images/logo-ltp-bopa.png" alt="Logo LTP Bopa">
                <div class="fin-logo-text">
                    <span class="fin-logo-name">LTP Bopa</span>
                    <span class="fin-logo-sub">Awards 2026</span>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Confetti animation
    (function() {
        const container = document.getElementById('confettiContainer');
        const colors = ['#D4AF37', '#E4C44E', '#F0D96A', '#10B981', '#EF4444', '#FFFFFF', '#8B6914'];

        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.width = (Math.random() * 8 + 5) + 'px';
            confetti.style.height = (Math.random() * 8 + 5) + 'px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            confetti.style.animationDuration = (Math.random() * 3 + 3) + 's';
            confetti.style.animationDelay = (Math.random() * 5) + 's';
            container.appendChild(confetti);

            setTimeout(() => confetti.remove(), 8000);
        }

        // Burst initial
        for (let i = 0; i < 40; i++) {
            setTimeout(() => createConfetti(), i * 100);
        }

        // Continuous subtle confetti
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                createConfetti();
            }
        }, 600);
    })();

    // Masquer le bandeau countdown sur cette page
    const marquee = document.getElementById('marqueeBar');
    if (marquee) {
        marquee.style.background = 'linear-gradient(90deg, #991B1B, #DC2626, #991B1B)';
        const text = marquee.querySelector('.announce-text');
        if (text) {
            text.innerHTML = '<i class="fas fa-flag-checkered"></i> Les votes sont clôturés &mdash; Merci pour votre participation !';
        }
    }
</script>
@endpush
