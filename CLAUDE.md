# CLAUDE.md

Ce fichier guide Claude Code pour le développement de ce projet.

## Projet

**LTP-BOPA VOTE** — Application web Laravel 12 pour le **Concours de l'Excellence 2025** organisé par le **Lycée Technique et Professionnel de Bopa** (Bénin). Les visiteurs votent pour leurs candidats favoris parmi les différentes filières en payant 100 FCFA par vote via Mobile Money (PawaPay STK Push).

## Stack technique

- **Backend :** Laravel 12 (PHP 8.2+), architecture MVC stricte
- **Base de données :** SQLite (dev) / PostgreSQL Neon (production)
- **Frontend :** Blade + Vanilla JS, Bootstrap 5 (CDN), Font Awesome 6.4
- **Fonts :** Playfair Display (titres), DM Sans (corps)
- **Assets :** Laravel Mix (webpack)
- **Paiement :** PawaPay API v2 (STK Push — Mobile Money Bénin)

## Commandes

```bash
# Installation
composer install && npm install
cp .env.example .env && php artisan key:generate

# Base de données
php artisan migrate --seed

# Développement
php artisan serve                # Serveur local (localhost:8000)
npm run watch                    # Compilation assets en continu

# Production
npm run prod                     # Compilation assets optimisée

# Tests
php artisan test                 # Tests PHPUnit
```

## Configuration base de données

**Dev (par défaut) :** SQLite — `DB_CONNECTION=sqlite` dans `.env`, fichier `database/database.sqlite`.

**Production (PostgreSQL Neon) :** Dans `.env` :
```env
DB_CONNECTION=pgsql
DB_HOST=ep-xxx.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=<password>
DB_SSLMODE=require
```

**Notes PostgreSQL :** Pas de `unsigned()`, pas d'`ENUM` natif (utiliser `string` + validation applicative), types natifs PostgreSQL.

## Système de paiement (PawaPay STK Push)

### PawaPay (passerelle unique)

- **Doc :** https://docs.pawapay.io
- **Base URL :** Production `https://api.pawapay.io/` | Sandbox `https://api.sandbox.pawapay.io/`
- **Auth :** Bearer token (header `Authorization: Bearer <token>`)
- **Bénin supporté :** Oui — providers `MTN_MOMO_BEN` et `MOOV_BEN`, devise `XOF`
- **Pas de décimales** pour les montants en XOF
- **STK Push :** Le votant saisit son numéro + opérateur, reçoit un prompt USSD pour confirmer

**Initier un dépôt (collecte de paiement) :**
```
POST /v2/deposits
{
  "depositId": "<uuid-v4-généré-côté-serveur>",
  "payer": {
    "type": "MMO",
    "accountDetails": {
      "phoneNumber": "22960000000",
      "provider": "MTN_MOMO_BEN"
    }
  },
  "amount": "100",
  "currency": "XOF",
  "clientReferenceId": "<transaction_id_du_vote>"
}
```

**Callback (webhook) :** PawaPay envoie un `POST` à l'URL callback configurée quand le dépôt atteint un statut final (`COMPLETED` ou `FAILED`). Vérifier la signature via les headers `Content-Digest`, `Signature`, `Signature-Input` (RFC-9421).

**Mapping opérateurs :** `MTN` → `MTN_MOMO_BEN`, `MOOV` → `MOOV_BEN`

**Mapping statuts PawaPay :** `COMPLETED` → `reussi`, `FAILED` → `echoue`, `REJECTED` → `echoue`, `CANCELLED` → `annule`

**Variables `.env` :**
```env
PAWAPAY_API_TOKEN=<token>
PAWAPAY_BASE_URL=https://api.sandbox.pawapay.io
PAWAPAY_CALLBACK_URL=https://mondomaine.com/api/payment/pawapay/callback
```

### Config `config/concours.php`

```php
'vote_price' => 100,          // FCFA par vote
'currency' => 'XOF',
'filieres' => ['DWM', 'PM', 'MMV', 'BTP', 'EA'],
'max_votes_per_transaction' => 100,
'pawapay' => [
    'api_token'    => env('PAWAPAY_API_TOKEN'),
    'base_url'     => env('PAWAPAY_BASE_URL', 'https://api.sandbox.pawapay.io'),
    'callback_url' => env('PAWAPAY_CALLBACK_URL'),
],
'payment_simulation' => env('PAYMENT_SIMULATION', false),
```

## Architecture

### Structure des fichiers clés

```
app/
├── Http/Controllers/
│   ├── CandidatController.php    # CRUD candidats, filtrage par filière, stats
│   ├── VoteController.php        # Création vote, consultation, stats
│   └── PaymentController.php     # Initiation paiement PawaPay, webhook, simulation
├── Models/
│   ├── Candidat.php              # Scopes: filiere(), populaires()
│   ├── Vote.php                  # Boot hook: auto transaction_id, auto-incrémente total_votes
│   ├── Don.php                   # Dons libres
│   └── Transaction.php           # deposit_id PawaPay, statut, operateur
├── Services/
│   └── PawapayService.php        # Intégration PawaPay API v2 (STK Push)
├── Console/Commands/
│   └── ReconcilierPaiements.php  # Tâche planifiée de réconciliation
bootstrap/
├── app.php                       # Config app Laravel 12 (routes, middleware, exceptions)
├── providers.php                 # Providers (AppServiceProvider uniquement)
config/concours.php               # Seul fichier config custom
routes/api.php                    # Routes API publiques
routes/web.php                    # Pages web
routes/console.php                # Tâches planifiées
public/css/app.css                # Styles (variables CSS, responsive)
public/js/app.js                  # Logique SPA
resources/views/
├── layouts/app.blade.php         # Layout maître
├── welcome.blade.php             # Page principale (vote)
├── don.blade.php                 # Page de don
└── mes-votes.blade.php           # Suivi des votes par téléphone
```

### Modèles et relations

| Modèle | Relations | Champs clés |
|--------|-----------|-------------|
| `Candidat` | hasMany(Vote) | nom, prenom, filiere, photo_url, description, total_votes |
| `Vote` | belongsTo(Candidat), hasOne(Transaction) | nombre_votes, montant_total, transaction_id, statut_paiement (en_attente/reussi/echoue), telephone, ip_address |
| `Don` | hasOne(Transaction) | montant, telephone, nom_donateur, statut, message |
| `Transaction` | belongsTo(Vote), belongsTo(Don) | deposit_id (UUID PawaPay), montant, statut, operateur (MTN/MOOV), response_data (JSON) |

### Flux de paiement (STK Push)

1. Votant choisit candidat + nombre de votes + saisit **téléphone** + sélectionne **opérateur** (MTN/MOOV)
2. `POST /api/votes` → Crée un vote en attente
3. `POST /api/payment/vote` → `PawapayService` envoie `POST /v2/deposits` à PawaPay
4. L'utilisateur reçoit un **prompt USSD** sur son téléphone et confirme avec son code PIN
5. Le frontend affiche "Confirmez sur votre téléphone" + **polling** `GET /api/payment/verifier?deposit_id=X` (toutes les 3s pendant 2 min)
6. `POST /api/payment/pawapay/callback` → Webhook PawaPay confirme le paiement côté serveur
7. Dev : `GET /api/payment/simulate?vote_id=X&statut=reussi` (nécessite `PAYMENT_SIMULATION=true`)

### Routes API (toutes publiques)

```
GET    /api/candidats                          # Liste candidats
GET    /api/candidats/filiere/{filiere}        # Par filière
GET    /api/candidats/statistiques             # Stats globales
GET    /api/candidats/{id}                     # Détail candidat
POST   /api/votes                              # Créer un vote
GET    /api/votes/{id}                         # Détail vote
GET    /api/votes/candidat/{candidatId}        # Votes d'un candidat
GET    /api/votes/statistiques/all             # Stats votes
POST   /api/payment/vote                       # Initier paiement vote (STK Push)
POST   /api/payment/don                        # Initier paiement don (STK Push)
POST   /api/payment/pawapay/callback           # Webhook PawaPay
GET    /api/payment/verifier                   # Vérifier statut dépôt
GET    /api/payment/rechercher                 # Recherche par téléphone
GET    /api/payment/simulate                   # Simulation (dev)
GET    /api/ping                               # Health check
```

## Conventions et règles

### Langue
- **Tout le contenu** visible par l'utilisateur est en **français** : labels, messages, notifications, commentaires
- Termes du domaine : candidat, filière, vote, reussi, echoue, en_attente

### Filières
- **DWM** (Développement Web et Mobile), **PM** (Producteur Multimédia), **MMV** (Menuiserie Métallique et Vitrerie), **BTP** (Bâtiment et Travaux Publics), **EA** (Électricité Automobile)

### Code PHP / Laravel — Bonnes pratiques

- **Validation :** Utiliser les Form Requests pour toute validation. Ne jamais faire confiance aux données entrantes.
- **Eloquent :** Scopes, accessors/mutators, relations. Pas de SQL brut sauf cas justifié.
- **Transactions DB :** `DB::transaction()` pour toute opération d'écriture multiple.
- **Services :** Extraire la logique métier complexe dans `app/Services/`. Les controllers restent fins (thin controllers).
- **Gestion d'erreurs :** Try/catch avec `Log::error()`. Retourner des réponses JSON cohérentes.
- **Réponses API :** Toujours `{'success': bool, 'message': string, ...}` comme enveloppe.
- **Sécurité :**
  - Eloquent/query builder uniquement (pas de concaténation SQL)
  - Valider et sanitiser toutes les entrées
  - Blade : `{{ }}` (échappé) par défaut, `{!! !!}` uniquement si nécessaire et justifié
  - Rate limiting sur les endpoints votes et paiement
  - Vérifier les signatures des webhooks PawaPay (Content-Digest RFC-9421)
  - Protection CSRF sur les routes web
  - Ne jamais exposer les clés API côté client
- **Nommage :** camelCase (méthodes/variables), PascalCase (classes), snake_case (colonnes DB, clés config)
- **PSR-12 :** Indentation 4 espaces, fins de ligne LF, UTF-8
- **DRY / KISS :** Pas de duplication, pas de sur-ingénierie, pas de fonctionnalités non demandées
- **Type hinting :** PHP 8.2+ (paramètres, retours, union types, enums si pertinent)
- **Laravel 12 :** Pas de Kernel HTTP/Console/Exception. Tout est dans `bootstrap/app.php`. Pas de fichiers config par défaut (sauf `config/concours.php`).

### Frontend — UI/UX

- **Design pro et intuitif** : Interface élégante, moderne, accessible à tout public (y compris non-technique)
- **Palette de couleurs** (respecter strictement les variables CSS existantes) :
  - Primary : `#2D4356` (bleu foncé)
  - Secondary/Accent : `#D4AF37` (doré)
  - Success : `#10B981` (vert)
- **Typographie :** Playfair Display (titres), DM Sans (corps)
- **Responsive :** Mobile-first, tous breakpoints Bootstrap
- **Accessibilité :** Contraste suffisant, labels sur inputs, navigation clavier
- **Animations :** Transitions CSS subtiles et fluides, pas de surcharge
- **UX :** Feedback immédiat (loaders, toasts, états boutons), parcours de vote en 3 clics max
- **Images :** Photos candidats bien cadrées, fallback si image manquante
- **Performance :** Vanilla JS, pas de librairies lourdes inutiles

### Tests

- Tests Feature pour chaque endpoint API (votes, paiement, candidats)
- Tests Unit pour la logique métier des modèles (boot hooks, scopes)
- Nommage en français : `test_un_vote_est_cree_avec_statut_en_attente()`

### Git

- Messages de commit en français : `type: description`
- Types : feat, fix, refactor, style, test, docs, chore

## Points d'attention

- **Pas d'authentification utilisateur** : Routes API publiques (vote anonyme). Sécuriser par rate limiting et validation.
- **Intégrité des votes** : Le boot hook du modèle Vote gère l'incrémentation atomique de `total_votes`. Ne jamais modifier `total_votes` manuellement.
- **PawaPay STK Push** : Pas de redirection vers une page checkout. Le votant confirme sur son téléphone via USSD. Le frontend fait du polling pour connaître le résultat.
- **Base de données** : SQLite en dev, PostgreSQL (Neon) en prod. Pas de `unsigned()`, pas d'`ENUM` natif dans les migrations.
- **Secrets** : `.env` jamais commité. Clés PawaPay dans `.env` uniquement.
- **Config** : Ne pas utiliser `url()` ou `route()` dans les fichiers `config/` (pas disponible au boot). Utiliser `env()` uniquement.
