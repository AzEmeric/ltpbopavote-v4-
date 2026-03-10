-- ============================================================
-- LTP-BOPA VOTE — Awards LTPBOPA 2025
-- Script SQL pour Supabase (PostgreSQL)
-- Copier-coller dans : Supabase > SQL Editor > New Query
-- ============================================================

-- ──────────────────────────────────────────
-- 1. TABLE : candidats
-- ──────────────────────────────────────────

CREATE TABLE IF NOT EXISTS candidats (
    id BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    filiere VARCHAR(10) NOT NULL,
    photo_url VARCHAR(500),
    description TEXT,
    total_votes INTEGER NOT NULL DEFAULT 0,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_candidats_filiere ON candidats (filiere);
CREATE INDEX idx_candidats_total_votes ON candidats (total_votes);
CREATE INDEX idx_candidats_actif ON candidats (actif);

-- ──────────────────────────────────────────
-- 2. TABLE : votes
-- ──────────────────────────────────────────

CREATE TABLE IF NOT EXISTS votes (
    id BIGSERIAL PRIMARY KEY,
    candidat_id BIGINT NOT NULL REFERENCES candidats(id) ON DELETE CASCADE,
    nombre_votes INTEGER NOT NULL DEFAULT 1,
    montant_total INTEGER NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    telephone VARCHAR(20),
    statut_paiement VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_votes_candidat_id ON votes (candidat_id);
CREATE INDEX idx_votes_statut_paiement ON votes (statut_paiement);
CREATE INDEX idx_votes_telephone ON votes (telephone);
CREATE INDEX idx_votes_created_at ON votes (created_at);

-- ──────────────────────────────────────────
-- 3. TABLE : dons
-- ──────────────────────────────────────────

CREATE TABLE IF NOT EXISTS dons (
    id BIGSERIAL PRIMARY KEY,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    nom_donateur VARCHAR(100),
    telephone VARCHAR(20) NOT NULL,
    montant INTEGER NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    message VARCHAR(500),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_dons_statut ON dons (statut);
CREATE INDEX idx_dons_telephone ON dons (telephone);
CREATE INDEX idx_dons_created_at ON dons (created_at);

-- ──────────────────────────────────────────
-- 4. TABLE : transactions (PawaPay)
-- ──────────────────────────────────────────

CREATE TABLE IF NOT EXISTS transactions (
    id BIGSERIAL PRIMARY KEY,
    vote_id BIGINT REFERENCES votes(id) ON DELETE SET NULL,
    don_id BIGINT REFERENCES dons(id) ON DELETE SET NULL,
    deposit_id VARCHAR(150),
    montant INTEGER NOT NULL,
    statut VARCHAR(30) NOT NULL DEFAULT 'en_attente',
    type VARCHAR(20) NOT NULL DEFAULT 'vote',
    telephone VARCHAR(20),
    operateur VARCHAR(10),
    metadata JSONB,
    response_data JSONB,
    processed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_transactions_deposit_id ON transactions (deposit_id);
CREATE INDEX idx_transactions_vote_id ON transactions (vote_id);
CREATE INDEX idx_transactions_don_id ON transactions (don_id);
CREATE INDEX idx_transactions_statut ON transactions (statut);
CREATE INDEX idx_transactions_type ON transactions (type);
CREATE INDEX idx_transactions_created_at ON transactions (created_at);

-- ──────────────────────────────────────────
-- 5. TABLE : migrations (requise par Laravel)
-- ──────────────────────────────────────────

CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Enregistrer les migrations comme deja executees
INSERT INTO migrations (migration, batch) VALUES
    ('2024_01_01_000001_create_candidats_table', 1),
    ('2024_01_01_000002_create_votes_table', 1),
    ('2024_01_01_000003_create_dons_table', 1),
    ('2024_01_01_000004_create_transactions_table', 1);

-- ──────────────────────────────────────────
-- 6. SEED : Candidats de demonstration
-- ──────────────────────────────────────────

INSERT INTO candidats (nom, prenom, filiere, photo_url, description, total_votes, actif, created_at, updated_at) VALUES

-- Filiere DWM - Developpement Web et Mobile
('KOUASSI', 'Marie', 'DWM', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400', 'Developpeuse web passionnee par le JavaScript et React. Leader dans son domaine avec une vision innovante.', 0, TRUE, NOW(), NOW()),
('ALABI', 'Jean', 'DWM', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', 'Expert en developpement mobile avec Flutter et React Native. Engage pour un developpement durable.', 0, TRUE, NOW(), NOW()),
('TOGBE', 'Sarah', 'DWM', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400', 'Full-stack developer specialisee en Node.js et MongoDB. Creative et stratege avec un parcours impressionnant.', 0, TRUE, NOW(), NOW()),

-- Filiere PM - Producteur Multimedia
('DOSSOU', 'Patrick', 'PM', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400', 'Producteur multimedia specialise en montage video et post-production. Visionnaire dans la creation de contenu.', 0, TRUE, NOW(), NOW()),
('AKPLOGAN', 'Estelle', 'PM', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400', 'Specialiste en production audiovisuelle et animation 3D. Talentueuse avec une signature unique.', 0, TRUE, NOW(), NOW()),
('HOUNSA', 'Franck', 'PM', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400', 'Createur de contenu digital et photographe. Passionne par la narration visuelle et le storytelling.', 0, TRUE, NOW(), NOW()),

-- Filiere MMV - Metier de la Mode et Vetement
('MENSAH', 'David', 'MMV', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400', 'Styliste creatif specialise en mode africaine contemporaine. Pionnier de l''innovation textile.', 0, TRUE, NOW(), NOW()),
('KOFFI', 'Lucie', 'MMV', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400', 'Designer de mode et couturiere avec une passion pour la haute couture. Artiste talentueuse et innovante.', 0, TRUE, NOW(), NOW()),

-- Filiere BTP - Batiment et Travaux Publics
('ADJOVI', 'Marc', 'BTP', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400', 'Passionne du genie civil et de la construction durable. Expert en conception de batiments modernes.', 0, TRUE, NOW(), NOW()),
('AGOSSOU', 'Grace', 'BTP', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400', 'Future ingenieure en travaux publics. Determinee a moderniser les infrastructures du Benin.', 0, TRUE, NOW(), NOW()),
('GNIMADI', 'Rodrigue', 'BTP', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400', 'Specialiste en maconnerie et beton arme. Engage dans la construction de qualite.', 0, TRUE, NOW(), NOW()),

-- Filiere EA - Electronique Appliquee
('HOUDEGBE', 'Aristide', 'EA', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400', 'Passionne d''electronique et de systemes embarques. Innovateur dans les solutions technologiques.', 0, TRUE, NOW(), NOW()),
('SOSSA', 'Christelle', 'EA', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400', 'Specialiste en circuits electroniques et maintenance industrielle. Pionniere dans son domaine.', 0, TRUE, NOW(), NOW());

-- ============================================================
-- FIN DU SCRIPT
-- 4 tables creees + 13 candidats inseres
-- ============================================================
