@extends('layouts.app')

@section('title', 'Mes votes — Awards LTPBOPA')

@section('content')

    <div class="lookup-page">
        <div class="container">
            <a href="/" class="donation-back">
                <i class="fas fa-arrow-left"></i>
                <span>Retour à l'accueil</span>
            </a>

            <div class="lookup-card">
                <div class="lookup-content">
                    <div class="donation-badge">
                        <i class="fas fa-search"></i>
                        <span>Suivi</span>
                    </div>
                    <h1 class="donation-title">Mes votes</h1>
                    <p class="donation-desc">
                        Vérifiez que vos votes ont bien été comptabilisés après le paiement.
                    </p>

                    <div class="donation-separator"></div>

                    <!-- Loading -->
                    <div id="lookupLoading" class="lookup-loading">
                        <div class="lookup-spinner"></div>
                        <p>Chargement...</p>
                    </div>

                    <!-- Résultats -->
                    <div id="lookupResults" class="lookup-results" style="display: none;">

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

                        <!-- Aucun résultat -->
                        <div id="lookupEmpty" class="lookup-empty" style="display: none;">
                            <i class="fas fa-inbox"></i>
                            <p>Vous n'avez pas encore voté depuis cet appareil.</p>
                            <span>Vos votes apparaîtront ici automatiquement après chaque paiement.</span>
                            <a href="/" style="display: inline-flex; align-items: center; gap: .5rem; margin-top: 1rem; color: var(--gold-500); text-decoration: none; font-weight: 600;">
                                <i class="fas fa-heart"></i> Voter maintenant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

@endsection

@push('scripts')
<script>
(async function() {
    const mesVotes = JSON.parse(localStorage.getItem('mes_votes') || '[]');
    const loadingDiv = document.getElementById('lookupLoading');
    const resultsDiv = document.getElementById('lookupResults');
    const votesDiv = document.getElementById('lookupVotes');
    const votesList = document.getElementById('lookupVotesList');
    const emptyDiv = document.getElementById('lookupEmpty');
    const summaryDiv = document.getElementById('lookupSummary');

    if (mesVotes.length === 0) {
        loadingDiv.style.display = 'none';
        resultsDiv.style.display = 'block';
        emptyDiv.style.display = 'block';
        return;
    }

    // Charger le statut de chaque vote depuis l'API
    const votesData = [];
    for (const v of mesVotes) {
        try {
            const res = await fetch(`${window.APP_CONFIG.apiUrl}/votes/${v.vote_id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success && data.vote) {
                votesData.push({
                    ...v,
                    statut: data.vote.statut_paiement,
                    nombre_votes: data.vote.nombre_votes,
                    montant: data.vote.montant_total,
                    candidat: data.vote.candidat ? (data.vote.candidat.prenom + ' ' + data.vote.candidat.nom) : v.candidat,
                    candidat_filiere: data.vote.candidat?.filiere || v.candidat_filiere,
                    candidat_photo: data.vote.candidat?.photo_url_complete || '',
                    candidat_total_votes: data.vote.candidat?.total_votes || 0,
                });
            }
        } catch (e) {
            votesData.push({ ...v, statut: 'inconnu' });
        }
    }

    loadingDiv.style.display = 'none';
    resultsDiv.style.display = 'block';

    if (votesData.length === 0) {
        emptyDiv.style.display = 'block';
        return;
    }

    // Résumé
    const confirmes = votesData.filter(v => v.statut === 'reussi');
    const enAttente = votesData.filter(v => v.statut === 'en_attente');
    const totalVotes = confirmes.reduce((s, v) => s + v.nombre_votes, 0);
    const totalMontant = confirmes.reduce((s, v) => s + v.montant, 0);

    summaryDiv.style.display = 'flex';
    summaryDiv.innerHTML = `
        <div class="lookup-summary-stat">
            <span class="lookup-summary-value">${totalVotes}</span>
            <span class="lookup-summary-label">votes confirmés</span>
        </div>
        <div class="lookup-summary-stat">
            <span class="lookup-summary-value">${totalMontant.toLocaleString('fr-FR')} F</span>
            <span class="lookup-summary-label">total payé</span>
        </div>
        ${enAttente.length > 0 ? `
        <div class="lookup-summary-stat lookup-summary-stat--warning">
            <span class="lookup-summary-value">${enAttente.length}</span>
            <span class="lookup-summary-label">en attente</span>
        </div>` : ''}
    `;

    // Liste des votes (du plus récent au plus ancien)
    votesDiv.style.display = 'block';
    votesList.innerHTML = votesData.reverse().map(v => {
        const statutLabel = { reussi: 'Confirmé', en_attente: 'En attente', echoue: 'Échoué', inconnu: 'Inconnu' };
        const isReussi = v.statut === 'reussi';
        const totalApres = v.candidat_total_votes || 0;
        const totalAvant = isReussi ? totalApres - v.nombre_votes : totalApres;

        return `
            <div class="lookup-item lookup-item--${v.statut}">
                <div class="lookup-item-header">
                    ${v.candidat_photo ? `
                    <div class="lookup-item-avatar">
                        <img src="${v.candidat_photo}" alt="${v.candidat}" onerror="this.src='/uploads/candidats/default.jpg'">
                    </div>` : ''}
                    <div class="lookup-item-left">
                        <span class="lookup-item-name">${v.candidat}</span>
                        <span class="lookup-item-detail">
                            <span class="lookup-item-filiere">${v.candidat_filiere || ''}</span>
                            ${v.nombre_votes} vote${v.nombre_votes > 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="lookup-item-right">
                        <span class="lookup-item-amount">${(v.montant || 0).toLocaleString('fr-FR')} F</span>
                        <span class="lookup-badge lookup-badge--${v.statut}">${statutLabel[v.statut] || v.statut}</span>
                    </div>
                </div>
                ${isReussi ? `
                <div class="lookup-scores">
                    <div class="lookup-score">
                        <span class="lookup-score-label">Avant</span>
                        <span class="lookup-score-value">${totalAvant.toLocaleString('fr-FR')}</span>
                    </div>
                    <div class="lookup-score-arrow">
                        <i class="fas fa-arrow-right"></i>
                        <span class="lookup-score-diff">+${v.nombre_votes}</span>
                    </div>
                    <div class="lookup-score lookup-score--after">
                        <span class="lookup-score-label">Après</span>
                        <span class="lookup-score-value">${totalApres.toLocaleString('fr-FR')}</span>
                    </div>
                </div>` : ''}
            </div>
        `;
    }).join('');
})();
</script>
@endpush
