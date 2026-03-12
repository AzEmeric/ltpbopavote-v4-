// ========================================
// LTP-BOPA VOTE — Concours de l'Excellence 2026
// ========================================

const CONFIG = window.APP_CONFIG;

// ========================================
// THEME TOGGLE (Dark / Light)
// ========================================

function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}

window.toggleTheme = toggleTheme;

// ========================================
// HAMBURGER MENU (mobile)
// ========================================

function toggleMobileMenu(btn) {
    btn.classList.toggle('active');
    const parent = btn.closest('.sticky-nav-inner') || btn.closest('.hero-top');
    const menu = parent?.querySelector('.mobile-menu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

window.toggleMobileMenu = toggleMobileMenu;

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

document.addEventListener('DOMContentLoaded', async () => {
    await loadCandidates();
    initEventListeners();
    initStickyNav();
    initRevealObserver();

    // Retour après paiement Moneroo : ouvrir la filière du candidat voté
    const params = new URLSearchParams(window.location.search);
    if (params.has('paymentId')) {
        const filiere = localStorage.getItem('retour_filiere');
        localStorage.removeItem('retour_filiere');
        if (filiere) {
            showFiliere(filiere);
        }
        const status = params.get('paymentStatus');
        if (status === 'success') {
            showSuccessModal();
        } else if (status === 'failed' || status === 'cancelled' || status === 'error') {
            showFailureModal();
        }
        // Nettoyer l'URL
        window.history.replaceState({}, '', '/');
    }
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

async function showFiliere(filiere) {
    appState.currentFiliere = filiere;

    document.querySelector('.filieres-section').style.display = 'none';

    const candidatsSection = document.getElementById('candidats');
    candidatsSection.style.display = 'block';

    const filiereNames = {
        DWM: 'Développement Web et Mobile',
        PM:  'Producteur Multimédia',
        MMV: 'Métier de la Mode et Vêtement',
        BTP: 'Bâtiment et Travaux Publics',
        EA:  'Électronique Appliquée'
    };

    document.getElementById('filiereBadge').textContent = filiere;
    document.getElementById('filiereTitle').textContent = filiereNames[filiere] || filiere;

    const loadingEl = document.getElementById('loadingCandidates');
    const gridEl = document.getElementById('candidatesGrid');

    loadingEl.style.display = 'block';
    gridEl.style.display = 'none';
    document.getElementById('noCandidates').style.display = 'none';

    // Si les candidats ne sont pas encore charges, attendre le chargement
    if (appState.candidats.length === 0) {
        await loadCandidates();
    }

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
            if (appState.currentFiliere) renderCandidates();
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

    // Progress bar max reference (700 votes = 100%)
    const maxVotes = 700;

    gridEl.innerHTML = candidats.map((c, i) => {
        const progress = Math.min(Math.round(((c.total_votes || 0) / maxVotes) * 100), 100);
        const rank = i + 1;
        const rankClass = rank <= 3 ? `rank-${rank}` : '';
        const photoUrl = c.photo_url_complete || c.photo_url || `${CONFIG.baseUrl}/uploads/candidats/default.jpg`;
        const totalVotes = c.total_votes || 0;

        return `
        <div class="candidate-card" style="animation-delay: ${i * 0.06}s">
            <div class="candidate-img">
                <img src="${photoUrl}"
                     alt="${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}"
                     loading="lazy"
                     onerror="this.src='${CONFIG.baseUrl}/uploads/candidats/default.jpg'">
                <div class="candidate-img-overlay"></div>
                ${rankClass ? `<span class="card-rank ${rankClass}">#${rank}</span>` : ''}
                <div class="card-votes">
                    <i class="fas fa-heart"></i>
                    <span>${formatNumber(totalVotes)}</span>
                </div>
            </div>
            <div class="candidate-body">
                <h3 class="candidate-name">${escapeHtml(c.prenom)} ${escapeHtml(c.nom)}</h3>
                <span class="candidate-tag">
                    <i class="fas fa-graduation-cap"></i>
                    ${escapeHtml(c.filiere)}
                </span>
                ${c.description ? `<p class="candidate-desc">${escapeHtml(c.description)}</p>` : ''}
                <div class="vote-progress">
                    <div class="progress-header">
                        <span>Popularité</span>
                        <span class="count">${formatNumber(totalVotes)} vote${totalVotes !== 1 ? 's' : ''}</span>
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

    // Reinitialiser le bouton payer
    const btn = document.getElementById('btnPay');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-paper-plane"></i> <span>Confirmer</span> <span class="btn-pay-amount" id="btnPayAmount">${CONFIG.votePrice} FCFA</span>`;
    }

    const overlay = document.getElementById('voteModalOverlay');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeVoteModal() {
    const overlay = document.getElementById('voteModalOverlay');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    appState.currentCandidat = null;

    // Toujours recharger les candidats a la fermeture du modal
    loadCandidates();
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
    if (v < 900) { v++; input.value = v; updatePaymentSummary(); highlightQuickVote(v); }
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
        // Mettre à jour le résumé pendant la saisie sans forcer la valeur
        input.addEventListener('input', () => {
            updatePaymentSummary();
            highlightQuickVote(parseInt(input.value) || 0);
        });
        // Valider et corriger quand l'utilisateur quitte le champ
        input.addEventListener('blur', () => {
            let v = parseInt(input.value) || 1;
            v = Math.max(1, Math.min(900, v));
            input.value = v;
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
// PAIEMENT (Redirection Moneroo)
// ========================================

async function processPayment() {
    if (!appState.currentCandidat) {
        showToast('Aucun candidat sélectionné', 'error');
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

        // 2. Initier le paiement Moneroo
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Redirection...</span>';

        const payRes = await fetch(`${CONFIG.apiUrl}/payment/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                vote_id: voteId
            })
        });

        const payData = await payRes.json();

        if (payData.success && payData.checkout_url) {
            // Sauvegarder le vote et la filière dans localStorage
            const mesVotes = JSON.parse(localStorage.getItem('mes_votes') || '[]');
            mesVotes.push({
                vote_id: voteId,
                candidat: appState.currentCandidat.prenom + ' ' + appState.currentCandidat.nom,
                candidat_filiere: appState.currentCandidat.filiere,
                nombre_votes: nombreVotes,
                montant: montantTotal,
                date: new Date().toLocaleString('fr-FR')
            });
            localStorage.setItem('mes_votes', JSON.stringify(mesVotes));
            localStorage.setItem('retour_filiere', appState.currentFiliere);
            localStorage.setItem('paiement_type', 'vote');
            window.location.href = payData.checkout_url;
        } else {
            throw new Error(payData.message || 'Erreur paiement');
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

    // Nav stats (elements may not exist on all pages)
    setTextContent('navTotalCandidats', formatNumber(totalCandidats));
    setTextContent('navTotalVotes', formatNumber(totalVotes));

    // Sticky nav stats (update if present)
    document.querySelectorAll('.sticky-stat span').forEach((el, i) => {
        if (i === 0) el.textContent = formatNumber(totalCandidats);
        if (i === 1) el.textContent = formatNumber(totalVotes);
    });
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

function scrollToFilieres() {
    hideFiliere();
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
// MODAL SUCCES PAIEMENT
// ========================================

function showSuccessModal() {
    const type = localStorage.getItem('paiement_type') || 'vote';
    localStorage.removeItem('paiement_type');

    const overlay = document.createElement('div');
    overlay.className = 'success-modal-overlay';
    overlay.onclick = (e) => { if (e.target === overlay) closeSuccessModal(overlay); };

    let detailsHtml = '';

    if (type === 'don') {
        const montant = localStorage.getItem('don_montant') || '';
        localStorage.removeItem('don_montant');
        detailsHtml = montant ? `
            <div class="success-modal-details">
                <div class="success-modal-detail">
                    <i class="fas fa-coins"></i>
                    <span>${formatNumber(parseInt(montant))} FCFA</span>
                </div>
            </div>` : '';
    } else {
        const mesVotes = JSON.parse(localStorage.getItem('mes_votes') || '[]');
        const dernierVote = mesVotes.length > 0 ? mesVotes[mesVotes.length - 1] : null;
        if (dernierVote) {
            detailsHtml = `
            <div class="success-modal-details">
                <div class="success-modal-detail">
                    <i class="fas fa-user"></i>
                    <span>${escapeHtml(dernierVote.candidat)}</span>
                </div>
                <div class="success-modal-detail">
                    <i class="fas fa-heart"></i>
                    <span>${dernierVote.nombre_votes} vote${dernierVote.nombre_votes > 1 ? 's' : ''}</span>
                </div>
                <div class="success-modal-detail">
                    <i class="fas fa-coins"></i>
                    <span>${formatNumber(dernierVote.montant)} FCFA</span>
                </div>
            </div>`;
        }
    }

    const titre = type === 'don' ? 'Merci pour votre don !' : 'Merci pour votre vote !';
    const texte = type === 'don'
        ? 'Votre don a bien été reçu. Merci pour votre générosité !'
        : 'Votre paiement a été confirmé et votre vote a bien été comptabilisé.';

    overlay.innerHTML = `
        <div class="success-modal">
            <div class="success-modal-confetti">
                <span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="success-modal-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-modal-title">${titre}</h2>
            <p class="success-modal-text">${texte}</p>
            ${detailsHtml}
            <button class="success-modal-btn" onclick="closeSuccessModal(this.closest('.success-modal-overlay'))">
                <i class="fas fa-thumbs-up"></i>
                <span>Super !</span>
            </button>
        </div>
    `;

    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    // Animation d'entree
    requestAnimationFrame(() => overlay.classList.add('active'));
}

function closeSuccessModal(overlay) {
    if (!overlay) return;
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    setTimeout(() => overlay.remove(), 300);
}

// ========================================
// MODAL ECHEC PAIEMENT
// ========================================

function showFailureModal() {
    const type = localStorage.getItem('paiement_type') || 'vote';
    localStorage.removeItem('paiement_type');

    const overlay = document.createElement('div');
    overlay.className = 'success-modal-overlay failure-modal-overlay';
    overlay.onclick = (e) => { if (e.target === overlay) closeSuccessModal(overlay); };

    let detailsHtml = '';
    if (type === 'vote') {
        const mesVotes = JSON.parse(localStorage.getItem('mes_votes') || '[]');
        const dernierVote = mesVotes.length > 0 ? mesVotes[mesVotes.length - 1] : null;
        if (dernierVote) {
            detailsHtml = `
            <div class="success-modal-details">
                <div class="success-modal-detail">
                    <i class="fas fa-user"></i>
                    <span>${escapeHtml(dernierVote.candidat)}</span>
                </div>
                <div class="success-modal-detail">
                    <i class="fas fa-coins"></i>
                    <span>${formatNumber(dernierVote.montant)} FCFA</span>
                </div>
            </div>`;
        }
    }

    overlay.innerHTML = `
        <div class="success-modal failure-modal">
            <div class="success-modal-icon failure-modal-icon">
                <i class="fas fa-times"></i>
            </div>
            <h2 class="success-modal-title">Paiement échoué</h2>
            <p class="success-modal-text">
                Votre paiement n'a pas abouti. Aucun montant n'a été débité.
                Vous pouvez réessayer à tout moment.
            </p>
            ${detailsHtml}
            <button class="success-modal-btn failure-modal-btn" onclick="closeSuccessModal(this.closest('.success-modal-overlay'))">
                <i class="fas fa-redo"></i>
                <span>Réessayer</span>
            </button>
        </div>
    `;

    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => overlay.classList.add('active'));
}

// ========================================
// DONATION (Redirection Moneroo)
// ========================================

function setDonation(amount) {
    document.getElementById('donationAmount').value = amount;
    document.querySelectorAll('.donation-amount-btn').forEach(btn => {
        const val = parseInt(btn.textContent.replace(/\s/g, '').replace('F', ''));
        btn.classList.toggle('active', val === amount);
    });
}

async function processDonation() {
    const input = document.getElementById('donationAmount');
    const amount = parseInt(input.value);

    if (!amount || amount < 100) {
        showToast('Veuillez entrer un montant minimum de 100 FCFA', 'error');
        return;
    }

    const btn = document.getElementById('btnDonate');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Redirection...</span>';

    try {
        const res = await fetch(`${CONFIG.apiUrl}/payment/don`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                montant: amount
            })
        });

        const data = await res.json();

        if (data.success && data.checkout_url) {
            localStorage.setItem('paiement_type', 'don');
            localStorage.setItem('don_montant', amount.toString());
            window.location.href = data.checkout_url;
        } else {
            throw new Error(data.message || 'Erreur paiement');
        }
    } catch (error) {
        console.error('Erreur don:', error);
        showToast(error.message || 'Une erreur est survenue', 'error');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
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
window.rechercherVotes = rechercherVotes;

// ========================================
// RECHERCHE PAR TELEPHONE (page /mes-votes)
// ========================================

async function rechercherVotes() {
    const phoneInput = document.getElementById('lookupPhone');
    if (!phoneInput) return;

    const telephone = phoneInput.value.replace(/\s/g, '');
    if (telephone.length < 8) {
        showToast('Veuillez entrer un numéro valide', 'error');
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

        // Resume global
        if (votes.length > 0) {
            const votesConfirmes = votes.filter(v => v.statut === 'reussi');
            const totalVotesConfirmes = votesConfirmes.reduce((sum, v) => sum + v.nombre_votes, 0);
            const totalMontant = votesConfirmes.reduce((sum, v) => sum + v.montant, 0);
            const votesEnAttente = votes.filter(v => v.statut === 'en_attente').length;

            summaryDiv.style.display = 'flex';
            summaryDiv.innerHTML = `
                <div class="lookup-summary-stat">
                    <span class="lookup-summary-value">${totalVotesConfirmes}</span>
                    <span class="lookup-summary-label">votes confirmés</span>
                </div>
                <div class="lookup-summary-stat">
                    <span class="lookup-summary-value">${totalMontant.toLocaleString('fr-FR')} F</span>
                    <span class="lookup-summary-label">total payé</span>
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
        showToast('Erreur de connexion. Réessayez.', 'error');
        console.error('Erreur recherche:', err);
    }
}

function renderVoteItem(v) {
    const isReussi = v.statut === 'reussi';
    const isEnAttente = v.statut === 'en_attente';

    const preuveHtml = isReussi ? `
        <div class="lookup-proof">
            <i class="fas fa-check-circle"></i>
            <span>
                <strong>+${v.nombre_votes}</strong> vote${v.nombre_votes > 1 ? 's' : ''} ajouté${v.nombre_votes > 1 ? 's' : ''} —
                ${v.candidat} a maintenant <strong>${v.candidat_total_votes.toLocaleString('fr-FR')}</strong> votes au total
            </span>
        </div>
    ` : '';

    const reverifierHtml = isEnAttente ? `
        <button class="lookup-reverify" onclick="reverifierVote(${v.id}, this)">
            <i class="fas fa-sync-alt"></i>
            Revérifier
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification...';

    try {
        // Chercher la transaction liee a ce vote
        const response = await fetch(`/api/votes/${voteId}`);
        const data = await response.json();

        if (data.success && data.vote?.transaction?.deposit_id) {
            // Verifier via l'API Moneroo
            const checkResponse = await fetch(`/api/payment/verifier?deposit_id=${data.vote.transaction.deposit_id}`);
            const checkData = await checkResponse.json();

            if (checkData.paiement_reussi) {
                showToast('Paiement confirmé ! Vos votes sont comptabilisés.', 'success');
            } else {
                showToast('Le paiement n\'est pas encore confirmé. Réessayez dans quelques minutes.', 'info');
            }
        } else {
            showToast('Impossible de vérifier ce vote pour le moment.', 'info');
        }

        // Relancer la recherche pour mettre a jour l'affichage
        rechercherVotes();

    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Revérifier';
        showToast('Erreur de connexion.', 'error');
    }
}

function formatStatut(statut) {
    const labels = {
        'reussi': 'Confirmé',
        'en_attente': 'En attente',
        'echoue': 'Échoué',
        'annule': 'Annulé'
    };
    return labels[statut] || statut;
}

window.reverifierVote = reverifierVote;
