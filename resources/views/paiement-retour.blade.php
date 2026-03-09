@extends('layouts.app')

@section('title', 'Resultat du paiement — LTP Bopa')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div id="retourCard" style="max-width: 480px; width: 100%; background: var(--surface-card, #111827); border-radius: 1.25rem; padding: 2.5rem; text-align: center; border: 1px solid var(--border-color, #1f2937);">

        <div id="retourLoading">
            <div style="font-size: 3rem; margin-bottom: 1rem;">
                <i class="fas fa-circle-notch fa-spin" style="color: var(--gold-500, #D4AF37);"></i>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.4rem;">Verification en cours...</h2>
        </div>

        <div id="retourSuccess" style="display: none;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(16,185,129,.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <i class="fas fa-check" style="font-size: 2rem; color: #10B981;"></i>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: .5rem;">Paiement reussi !</h2>
            <p style="color: var(--text-muted, #9ca3af); font-size: .95rem; margin-bottom: 1.5rem;">
                Votre vote a ete comptabilise avec succes. Merci pour votre soutien !
            </p>
            <div id="retourDetails" style="background: var(--surface-hover, #1a2332); border-radius: .75rem; padding: 1rem; margin-bottom: 1.5rem; text-align: left; font-size: .85rem; color: var(--text-muted, #9ca3af);">
            </div>
            <a href="/" style="display: inline-flex; align-items: center; gap: .5rem; background: var(--gold-500, #D4AF37); color: #000; padding: .75rem 1.5rem; border-radius: .75rem; text-decoration: none; font-weight: 600; font-size: .9rem;">
                <i class="fas fa-arrow-left"></i> Retour a l'accueil
            </a>
        </div>

        <div id="retourFailed" style="display: none;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(239,68,68,.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <i class="fas fa-xmark" style="font-size: 2rem; color: #EF4444;"></i>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: .5rem;">Paiement echoue</h2>
            <p style="color: var(--text-muted, #9ca3af); font-size: .95rem; margin-bottom: 1.5rem;">
                Le paiement n'a pas abouti. Votre vote n'a pas ete comptabilise. Vous pouvez reessayer.
            </p>
            <a href="/" style="display: inline-flex; align-items: center; gap: .5rem; background: var(--gold-500, #D4AF37); color: #000; padding: .75rem 1.5rem; border-radius: .75rem; text-decoration: none; font-weight: 600; font-size: .9rem;">
                <i class="fas fa-arrow-left"></i> Retour a l'accueil
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(async function() {
    const params = new URLSearchParams(window.location.search);
    const paymentId = params.get('paymentId');
    const paymentStatus = params.get('paymentStatus');

    const loadingEl = document.getElementById('retourLoading');
    const successEl = document.getElementById('retourSuccess');
    const failedEl = document.getElementById('retourFailed');
    const detailsEl = document.getElementById('retourDetails');

    if (!paymentId) {
        loadingEl.style.display = 'none';
        failedEl.style.display = 'block';
        return;
    }

    loadingEl.style.display = 'none';

    if (paymentStatus === 'success') {
        successEl.style.display = 'block';
    } else {
        failedEl.style.display = 'block';
    }
})();
</script>
@endpush
