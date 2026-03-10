@extends('layouts.app')

@section('title', 'Resultat du paiement — LTP Bopa')

@section('content')
<div class="paiement-retour-page">
    <div class="paiement-retour-card">
        <div class="paiement-retour-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="paiement-retour-title">Merci !</h2>
        <p class="paiement-retour-text">
            Verifiez le statut de vos votes sur la page "Mes votes" en utilisant votre numero de telephone.
        </p>
        <a href="/" class="paiement-retour-btn">
            <i class="fas fa-arrow-left"></i> Retour a l'accueil
        </a>
    </div>
</div>

<style>
.paiement-retour-page {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 2rem 1rem;
    background: var(--bg-body);
    transition: background .4s var(--ease);
}
.paiement-retour-card {
    max-width: 480px; width: 100%;
    background: var(--bg-card);
    border-radius: var(--radius-2xl);
    padding: 2.5rem;
    text-align: center;
    border: 1px solid var(--border-default);
    box-shadow: var(--shadow-lg);
    transition: background .4s var(--ease), border-color .4s var(--ease);
}
.paiement-retour-icon {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: var(--green-light, rgba(16,185,129,.12));
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
}
.paiement-retour-icon i {
    font-size: 2rem; color: var(--green);
}
.paiement-retour-title {
    font-family: var(--font-display);
    font-size: 1.5rem;
    color: var(--text-heading);
    margin-bottom: .5rem;
    transition: color .4s var(--ease);
}
.paiement-retour-text {
    color: var(--text-muted);
    font-size: .95rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    transition: color .4s var(--ease);
}
.paiement-retour-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--gold-500), var(--gold-600));
    color: var(--navy-950);
    padding: .75rem 1.5rem;
    border-radius: var(--radius);
    text-decoration: none;
    font-weight: 700; font-size: .9rem;
    transition: .3s var(--ease);
    box-shadow: var(--shadow-gold);
}
.paiement-retour-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(212,175,55,.35);
}

@media (max-width: 480px) {
    .paiement-retour-card { padding: 2rem 1.25rem; }
    .paiement-retour-title { font-size: 1.3rem; }
    .paiement-retour-icon { width: 64px; height: 64px; }
    .paiement-retour-icon i { font-size: 1.5rem; }
}
</style>
@endsection
