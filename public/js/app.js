// ========================================
// LTP-BOPA VOTE — Concours de l'Excellence 2025
// ========================================

const CONFIG = window.APP_CONFIG;

let appState = {
    candidats: [],
    currentCandidat: null,
    nombreVotes: 1,
    isLoading: false,
    currentFiliere: null,
    candidatsParFiliere: { DWM: [], PM: [], MMV: [], BTP: [], EA: [] }
};

// ========================================
// INITIALISATION
// ========================================

document.addEventListener('DOMContentLoaded', () => {
    loadCandidates();
    initEventListeners();
    initStickyNav();
    initRevealObserver();
});

// ========================================
// STICKY NAV
// ========================================

function initStickyNav() {
    const nav = document.getElementById('stickyNav');
    if (!nav) return;

    let lastScroll = 0;
    const heroHeight = document.querySelector('.hero')?.offsetHeight || 600;

    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        if (y > heroHeight * 0.7) {
            nav.classList.add('visible');
        } else {
            nav.classList.remove('visible');
        }
        lastScroll = y;
    }, { passive: true });
}

// ========================================
// REVEAL ON SCROLL
// ========================================

function initRevealObserver() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

// ========================================
// FILIERES
// ========================================

function showFiliere(filiere) {
    appState.currentFiliere = filiere;

    document.querySelector('.filieres-section').style.display = 'none';

    const candidatsSection = document.getElementById('candidats');
    candidatsSection.style.display = 'block';

    const filiereNames = {
        DWM: 'Developpement Web et Mobile',
        PM:  'Producteur Multimedia',
        MMV: 'Metier de la Mode et Vetement',
        BTP: 'Batiment et Travaux Publics',
        EA:  'Electronique Appliquee'
    };

    document.getElementById('filiereBadge').textContent = filiere;
    document.getElementById('filiereTitle').textContent = filiereNames[filiere] || filiere;

    const loadingEl = document.getElementById('loadingCandidates');
    const gridEl = document.getElementById('candidatesGrid');

    loadingEl.style.display = 'block';
    gridEl.style.display = 'none';
    document.getElementById('noCandidates').style.display = 'none';

    setTimeout(() => {
        loadingEl.style.display = 'none';
        renderCandidates();
        candidatsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 400);
}

function hideFiliere() {
    appState.currentFiliere = null;
    document.querySelector('.filieres-section').style.display = 'block';
    document.getElementById('candidats').style.display = 'none';

    const filieresSection = document.querySelector('.filieres-section');
    filieresSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ========================================
// CANDIDATS
// ========================================

async function loadCandidates() {
    try {
        const response = await fetch(`${CONFIG.apiUrl}/candidats`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error('Erreur lors du chargement');

        const data = await response.json();

        if (data.success) {
            appState.candidats = data.candidats || [];
            organizeCandidatesByFiliere();
            updateFiliereCounters();
            updateStats();
        }
    } catch (error) {
        console.error('Erreur chargement candidats:', error);
    }
}

function organizeCandidatesByFiliere() {
    appState.candidatsParFiliere = { DWM: [], PM: [], MMV: [], BTP: [], EA: [] };

    appState.candidats.forEach(c => {
        if (appState.candidatsParFiliere[c.filiere]) {
            appState.candidatsParFiliere[c.filiere].push(c);
        }
    });
}

function updateFiliereCounters() {
    ['DWM', 'PM', 'MMV', 'BTP', 'EA'].forEach(f => {
        const el = document.getElementById(`count-${f}`);
        if (el) el.textContent = appState.candidatsParFiliere[f].length;
    });
}

function renderCandidates() {
    const gridEl = document.getElementById('candidatesGrid');
    const emptyEl = document.getElementById('noCandidates');

    if (!appState.currentFiliere) return;

    const candidats = appState.candidatsParFiliere[appState.currentFiliere] || [];

    if (candidats.length === 0) {
        gridEl.style.display = 'none';
        emptyEl.style.display = 'block';
        return;
    }

    emptyEl.style.display = 'none';
    gridEl.style.display = 'grid';

    // Find max votes for progress bar
    const maxVotes = Math.max(...candidats.map(c => c.total_votes || 0), 1);

    gridEl.innerHTML = candidats.map((c, i) => {
        const progress = maxVotes > 0 ? Math.round(((c.total_votes || 0) / maxVotes) * 100) : 0;
        const rank = i + 1;
        const rankClass = rank <= 3 ? `rank-${rank}` : '';
        const photoUrl = c.photo_url_complete || c.photo_url || `${CONFIG.baseUrl}/uploads/candidats/default.jpg`;

        return `
        <div class="candidate-card" style="animation-delay: ${i * 0.08}s">
            <div class="candidate-img">
                <img src="${photoUrl}"
                     alt="${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}"
                     loading="lazy"
                     onerror="this.src='${CONFIG.baseUrl}/uploads/candidats/default.jpg'">
                <div class="candidate-img-overlay"></div>
                ${rankClass ? `<span class="card-rank ${rankClass}">#${rank}</span>` : ''}
                <div class="card-votes">
                    <i class="fas fa-heart"></i>
                    <span>${formatNumber(c.total_votes || 0)}</span>
                </div>
            </div>
            <div class="candidate-body">
                <h3 class="candidate-name">${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}</h3>
                <span class="candidate-tag">
                    <i class="fas fa-graduation-cap"></i>
                    ${escapeHtml(c.filiere)}
                </span>
                <p class="candidate-desc">${escapeHtml(c.description || '')}</p>
                <div class="vote-progress">
                    <div class="progress-header">
                        <span>Popularite</span>
                        <span class="count">${formatNumber(c.total_votes || 0)} votes</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: ${progress}%"></div>
                    </div>
                </div>
                <button class="btn-vote" onclick="openVoteModal(${c.id})">
                    <i class="fas fa-heart"></i>
                    <span>Voter pour ${escapeHtml(c.prenom)}</span>
                </button>
            </div>
        </div>`;
    }).join('');
}

// ========================================
// MODAL
// ========================================

function openVoteModal(candidatId) {
    const candidat = appState.candidats.find(c => c.id === candidatId);
    if (!candidat) {
        showToast('Candidat introuvable', 'error');
        return;
    }

    appState.currentCandidat = candidat;
    appState.nombreVotes = 1;

    document.getElementById('modalCandidatNom').textContent = `${candidat.prenom} ${candidat.nom}`;
    document.getElementById('modalCandidatFiliere').textContent = candidat.filiere;
    document.getElementById('modalCandidatPhoto').src = candidat.photo_url_complete || candidat.photo_url;
    document.getElementById('modalCandidatPhoto').alt = `${candidat.prenom} ${candidat.nom}`;
    document.getElementById('nombreVotes').value = 1;

    updatePaymentSummary();
    highlightQuickVote(1);

    const overlay = document.getElementById('voteModalOverlay');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeVoteModal() {
    const overlay = document.getElementById('voteModalOverlay');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    appState.currentCandidat = null;
}

function handleOverlayClick(e) {
    if (e.target === e.currentTarget) {
        closeVoteModal();
    }
}

function setVotes(n) {
    document.getElementById('nombreVotes').value = n;
    appState.nombreVotes = n;
    updatePaymentSummary();
    highlightQuickVote(n);
}

function incrementVotes() {
    const input = document.getElementById('nombreVotes');
    let v = parseInt(input.value) || 1;
    if (v < 100) { v++; input.value = v; updatePaymentSummary(); highlightQuickVote(v); }
}

function decrementVotes() {
    const input = document.getElementById('nombreVotes');
    let v = parseInt(input.value) || 1;
    if (v > 1) { v--; input.value = v; updatePaymentSummary(); highlightQuickVote(v); }
}

function highlightQuickVote(n) {
    document.querySelectorAll('.quick-vote-btn').forEach(btn => {
        btn.classList.toggle('active', parseInt(btn.textContent) === n);
    });
}

function updatePaymentSummary() {
    const n = parseInt(document.getElementById('nombreVotes').value) || 1;
    const total = n * CONFIG.votePrice;

    document.getElementById('summaryVotes').textContent = n;
    document.getElementById('totalAmount').textContent = `${formatNumber(total)} FCFA`;

    const btnAmount = document.getElementById('btnPayAmount');
    if (btnAmount) btnAmount.textContent = `${formatNumber(total)} FCFA`;
}

function initEventListeners() {
    const input = document.getElementById('nombreVotes');
    if (input) {
        input.addEventListener('input', (e) => {
            let v = parseInt(e.target.value) || 1;
            v = Math.max(1, Math.min(100, v));
            e.target.value = v;
            updatePaymentSummary();
            highlightQuickVote(v);
        });
    }

    // ESC to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeVoteModal();
    });
}

// ========================================
// PAIEMENT
// ========================================

async function processPayment() {
    if (!appState.currentCandidat) {
        showToast('Aucun candidat selectionne', 'error');
        return;
    }

    const nombreVotes = parseInt(document.getElementById('nombreVotes').value) || 1;
    const montantTotal = nombreVotes * CONFIG.votePrice;

    const btn = document.getElementById('btnPay');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Traitement...</span>';

    try {
        // 1. Creer le vote
        const voteRes = await fetch(`${CONFIG.apiUrl}/votes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                candidat_id: appState.currentCandidat.id,
                nombre_votes: nombreVotes,
                montant_total: montantTotal
            })
        });

        const voteData = await voteRes.json();
        if (!voteData.success) throw new Error(voteData.message || 'Erreur creation vote');

        const voteId = voteData.vote_id;

        // 2. Paiement
        if (CONFIG.paymentSimulation) {
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Validation...</span>';
            await sleep(1500);

            const simRes = await fetch(`${CONFIG.apiUrl}/payment/simulate?vote_id=${voteId}&statut=reussi`);
            const simData = await simRes.json();

            if (simData.success) {
                closeVoteModal();
                showToast(`Vote valide ! ${nombreVotes} vote(s) pour ${appState.currentCandidat.prenom}`, 'success');
                setTimeout(() => loadCandidates(), 1000);
            } else {
                throw new Error(simData.message || 'Echec simulation');
            }
        } else {
            const payRes = await fetch(`${CONFIG.apiUrl}/payment/initier`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                },
                body: JSON.stringify({ vote_id: voteId })
            });

            const payData = await payRes.json();

            if (payData.success && payData.payment_url) {
                closeVoteModal();
                showToast('Redirection vers le paiement...', 'info');
                setTimeout(() => { window.location.href = payData.payment_url; }, 800);
            } else {
                throw new Error(payData.message || 'Erreur paiement');
            }
        }
    } catch (error) {
        console.error('Erreur paiement:', error);
        showToast(error.message || 'Une erreur est survenue', 'error');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
}

// ========================================
// STATS
// ========================================

function updateStats() {
    const totalCandidats = appState.candidats.length;
    const totalVotes = appState.candidats.reduce((s, c) => s + (c.total_votes || 0), 0);

    // Hero stats
    animateNumber('totalCandidats', totalCandidats);
    animateNumber('totalVotes', totalVotes);
    animateNumber('statCandidats', totalCandidats);
    animateNumber('statVotes', totalVotes);

    // Nav stats
    setTextContent('navTotalCandidats', formatNumber(totalCandidats));
    setTextContent('navTotalVotes', formatNumber(totalVotes));
}

function animateNumber(id, target) {
    const el = document.getElementById(id);
    if (!el) return;

    const duration = 1200;
    const start = parseInt(el.textContent) || 0;
    const diff = target - start;
    if (diff === 0) { el.textContent = formatNumber(target); return; }

    const startTime = performance.now();
    function step(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        el.textContent = formatNumber(Math.round(start + diff * eased));
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

// ========================================
// UTILS
// ========================================

function formatNumber(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function setTextContent(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

function scrollToFilieres() {
    const el = document.querySelector('.filieres-section');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function scrollToDonation() {
    const el = document.getElementById('donation');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ========================================
// TOAST SYSTEM
// ========================================

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('leaving');
        setTimeout(() => toast.remove(), 350);
    }, 4000);
}

// ========================================
// DONATION
// ========================================

function setDonation(amount) {
    document.getElementById('donationAmount').value = amount;
    document.querySelectorAll('.donation-amount-btn').forEach(btn => {
        const val = parseInt(btn.textContent.replace(/\s/g, '').replace('F', ''));
        btn.classList.toggle('active', val === amount);
    });
}

function processDonation() {
    const input = document.getElementById('donationAmount');
    const amount = parseInt(input.value);

    if (!amount || amount < 100) {
        showToast('Veuillez entrer un montant minimum de 100 FCFA', 'error');
        return;
    }

    showToast('Fonctionnalite de don bientot disponible !', 'info');
}

// ========================================
// THEME TOGGLE
// ========================================

function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);

    // Mettre a jour le meta theme-color
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', next === 'dark' ? '#070E18' : '#F8FAFC');
    }
}

// ========================================
// GLOBAL EXPORTS
// ========================================

window.showFiliere = showFiliere;
window.hideFiliere = hideFiliere;
window.openVoteModal = openVoteModal;
window.closeVoteModal = closeVoteModal;
window.handleOverlayClick = handleOverlayClick;
window.incrementVotes = incrementVotes;
window.decrementVotes = decrementVotes;
window.setVotes = setVotes;
window.processPayment = processPayment;
window.scrollToFilieres = scrollToFilieres;
window.scrollToDonation = scrollToDonation;
window.setDonation = setDonation;
window.processDonation = processDonation;
window.toggleTheme = toggleTheme;
window.rechercherVotes = rechercherVotes;

// ========================================
// RECHERCHE PAR TELEPHONE (page /mes-votes)
// ========================================

async function rechercherVotes() {
    const phoneInput = document.getElementById('lookupPhone');
    if (!phoneInput) return;

    const telephone = phoneInput.value.replace(/\s/g, '');
    if (telephone.length < 8) {
        showToast('Veuillez entrer un numero valide', 'error');
        phoneInput.focus();
        return;
    }

    const resultsDiv = document.getElementById('lookupResults');
    const loadingDiv = document.getElementById('lookupLoading');
    const votesDiv = document.getElementById('lookupVotes');
    const donsDiv = document.getElementById('lookupDons');
    const emptyDiv = document.getElementById('lookupEmpty');
    const summaryDiv = document.getElementById('lookupSummary');
    const votesList = document.getElementById('lookupVotesList');
    const donsList = document.getElementById('lookupDonsList');

    resultsDiv.style.display = 'none';
    loadingDiv.style.display = 'flex';

    try {
        const response = await fetch(`/api/payment/rechercher?telephone=${encodeURIComponent(telephone)}`);
        const data = await response.json();

        loadingDiv.style.display = 'none';

        if (!data.success) {
            showToast(data.message || 'Erreur lors de la recherche', 'error');
            return;
        }

        const votes = data.resultats?.votes || [];
        const dons = data.resultats?.dons || [];
        const hasResults = votes.length > 0 || dons.length > 0;

        resultsDiv.style.display = 'block';

        // Résumé global
        if (votes.length > 0) {
            const votesConfirmes = votes.filter(v => v.statut === 'reussi');
            const totalVotesConfirmes = votesConfirmes.reduce((sum, v) => sum + v.nombre_votes, 0);
            const totalMontant = votesConfirmes.reduce((sum, v) => sum + v.montant, 0);
            const votesEnAttente = votes.filter(v => v.statut === 'en_attente').length;

            summaryDiv.style.display = 'flex';
            summaryDiv.innerHTML = `
                <div class="lookup-summary-stat">
                    <span class="lookup-summary-value">${totalVotesConfirmes}</span>
                    <span class="lookup-summary-label">votes confirmes</span>
                </div>
                <div class="lookup-summary-stat">
                    <span class="lookup-summary-value">${totalMontant.toLocaleString('fr-FR')} F</span>
                    <span class="lookup-summary-label">total paye</span>
                </div>
                ${votesEnAttente > 0 ? `
                <div class="lookup-summary-stat lookup-summary-stat--warning">
                    <span class="lookup-summary-value">${votesEnAttente}</span>
                    <span class="lookup-summary-label">en attente</span>
                </div>` : ''}
            `;
        } else {
            summaryDiv.style.display = 'none';
        }

        // Votes
        if (votes.length > 0) {
            votesDiv.style.display = 'block';
            votesList.innerHTML = votes.map(v => renderVoteItem(v)).join('');
        } else {
            votesDiv.style.display = 'none';
        }

        // Dons
        if (dons.length > 0) {
            donsDiv.style.display = 'block';
            donsList.innerHTML = dons.map(d => `
                <div class="lookup-item">
                    <div class="lookup-item-left">
                        <span class="lookup-item-name">Don</span>
                        <span class="lookup-item-detail">Ref : ${d.transaction_id || '—'} · ${d.date}</span>
                    </div>
                    <div class="lookup-item-right">
                        <span class="lookup-item-amount">${d.montant.toLocaleString('fr-FR')} F</span>
                        <span class="lookup-badge lookup-badge--${d.statut}">${formatStatut(d.statut)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            donsDiv.style.display = 'none';
        }

        if (!hasResults) {
            emptyDiv.style.display = 'block';
            votesDiv.style.display = 'none';
            donsDiv.style.display = 'none';
        } else {
            emptyDiv.style.display = 'none';
        }

    } catch (err) {
        loadingDiv.style.display = 'none';
        showToast('Erreur de connexion. Reessayez.', 'error');
        console.error('Erreur recherche:', err);
    }
}

function renderVoteItem(v) {
    const isReussi = v.statut === 'reussi';
    const isEnAttente = v.statut === 'en_attente';

    // Preuve : le candidat a bien X votes au total, dont Y de ce votant
    const preuveHtml = isReussi ? `
        <div class="lookup-proof">
            <i class="fas fa-check-circle"></i>
            <span>
                <strong>+${v.nombre_votes}</strong> vote${v.nombre_votes > 1 ? 's' : ''} ajoute${v.nombre_votes > 1 ? 's' : ''} —
                ${v.candidat} a maintenant <strong>${v.candidat_total_votes.toLocaleString('fr-FR')}</strong> votes au total
            </span>
        </div>
    ` : '';

    // Bouton revérifier pour les votes en attente
    const reverifierHtml = isEnAttente ? `
        <button class="lookup-reverify" onclick="reverifierVote(${v.id}, this)">
            <i class="fas fa-sync-alt"></i>
            Reverifier
        </button>
    ` : '';

    return `
        <div class="lookup-item lookup-item--${v.statut}">
            <div class="lookup-item-header">
                <div class="lookup-item-avatar">
                    <img src="${v.candidat_photo}" alt="${v.candidat}" onerror="this.src='/uploads/candidats/default.jpg'">
                </div>
                <div class="lookup-item-left">
                    <span class="lookup-item-name">${v.candidat}</span>
                    <span class="lookup-item-detail">${v.candidat_filiere} · ${v.nombre_votes} vote${v.nombre_votes > 1 ? 's' : ''} · ${v.date}</span>
                </div>
                <div class="lookup-item-right">
                    <span class="lookup-item-amount">${v.montant.toLocaleString('fr-FR')} F</span>
                    <span class="lookup-badge lookup-badge--${v.statut}">${formatStatut(v.statut)}</span>
                </div>
            </div>
            ${preuveHtml}
            ${reverifierHtml}
        </div>
    `;
}

async function reverifierVote(voteId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verification...';

    try {
        // Chercher la transaction liée à ce vote
        const response = await fetch(`/api/votes/${voteId}`);
        const data = await response.json();

        if (data.success && data.vote?.transaction?.moneroo_id) {
            // Forcer la vérification via l'API Moneroo
            const checkResponse = await fetch(`/api/payment/retour?paymentId=${data.vote.transaction.moneroo_id}`);
            const checkData = await checkResponse.json();

            if (checkData.paiement_reussi) {
                showToast('Paiement confirme ! Vos votes sont comptabilises.', 'success');
            } else {
                showToast('Le paiement n\'est pas encore confirme. Reessayez dans quelques minutes.', 'warning');
            }
        } else {
            showToast('Impossible de verifier ce vote pour le moment.', 'warning');
        }

        // Relancer la recherche pour mettre à jour l'affichage
        rechercherVotes();

    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Reverifier';
        showToast('Erreur de connexion.', 'error');
    }
}

function formatStatut(statut) {
    const labels = {
        'reussi': 'Confirme',
        'en_attente': 'En attente',
        'echoue': 'Echoue',
        'annule': 'Annule'
    };
    return labels[statut] || statut;
}

window.reverifierVote = reverifierVote;
