@extends('layouts.app')

@section('title', 'Resultat du paiement — LTP Bopa')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div style="max-width: 480px; width: 100%; background: var(--surface-card, #111827); border-radius: 1.25rem; padding: 2.5rem; text-align: center; border: 1px solid var(--border-color, #1f2937);">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(16,185,129,.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
            <i class="fas fa-check" style="font-size: 2rem; color: #10B981;"></i>
        </div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: .5rem;">Merci !</h2>
        <p style="color: var(--text-muted, #9ca3af); font-size: .95rem; margin-bottom: 1.5rem;">
            Verifiez le statut de vos votes sur la page "Mes votes" en utilisant votre numero de telephone.
        </p>
        <a href="/" style="display: inline-flex; align-items: center; gap: .5rem; background: var(--gold-500, #D4AF37); color: #000; padding: .75rem 1.5rem; border-radius: .75rem; text-decoration: none; font-weight: 600; font-size: .9rem;">
            <i class="fas fa-arrow-left"></i> Retour a l'accueil
        </a>
    </div>
</div>
@endsection
