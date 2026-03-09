# CLAUDE.md

Ce fichier guide Claude Code pour le développement de ce projet.

## Projet

**LTP-BOPA VOTE** — Application web Laravel 12 pour le **Concours de l'Excellence 2025** organisé par le **Lycée Technique et Professionnel de Bopa** (Bénin). Les visiteurs votent pour leurs candidats favoris parmi les différentes filières en payant 100 FCFA par vote via Mobile Money (PawaPay / FeexPay).

## Stack technique

- **Backend :** Laravel 12 (PHP 8.2+), architecture MVC stricte
- **Base de données :** SQLite (dev) / **Supabase** PostgreSQL (production)
- **Frontend :** Blade + Vanilla JS, Bootstrap 5 (CDN), Font Awesome 6.4
- **Fonts :** Playfair Display (titres), DM Sans (corps)
- **Assets :** Laravel Mix (webpack)
- **Paiement :** PawaPay API v2 + FeexPay SDK PHP (double passerelle Mobile Money)

## Commandes

```bash
# Installation
composer install && npm install
cp .env.example .env && php artisan key:generate

# Base de données (Supabase PostgreSQL)
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

**Production (Supabase PostgreSQL) :** Dans `.env` :
```env
DB_CONNECTION=pgsql
DB_HOST=db.<project-ref>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<supabase-db-password>
```

**Notes PostgreSQL :** Pas de `unsigned()`, pas d'`ENUM` natif (utiliser `string` + validation applicative), types natifs PostgreSQL.

## Système de paiement (PawaPay + FeexPay)

### PawaPay (passerelle principale)

- **Doc :** https://docs.pawapay.io
- **Base URL :** Production `https://api.pawapay.io/` | Sandbox `https://api.sandbox.pawapay.io/`
- **Auth :** Bearer token (header `Authorization: Bearer <token>`)
- **Bénin supporté :** Oui — providers `MTN_MOMO_BEN` et `MOOV_BEN`, devise `XOF`
- **Pas de décimales** pour les montants en XOF

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

**Callback (webhook) :** PawaPay envoie un `POST` à l'URL callback configurée quand le dépôt atteint un statut final (`COMPLETED` ou `FAILED`). Vérifier la signature via les headers `Content-Digest`, `Signature`, `Signature-Input`.

**Variables `.env` :**
```env
PAWAPAY_API_TOKEN=<token>
PAWAPAY_BASE_URL=https://api.sandbox.pawapay.io
PAWAPAY_CALLBACK_URL=https://mondomaine.com/api/payment/pawapay/callback
```

### FeexPay (passerelle secondaire)

- **Doc :** https://docs.feexpay.me
- **SDK PHP :** `composer require feexpayme/feexpay-sdk-php`
- **Auth :** Shop ID + API Token
- **Opérateurs Bénin :** MTN, MOOV
- **Devises :** XOF, USD, EUR

**Utilisation SDK :**
```php
$feexpay = new FeexpayClass($shopId, $apiToken, $callbackUrl, "SANDBOX");
$response = $feexpay->paiementLocal($montant, $telephone, "MTN", $nom, $email);
$status = $feexpay->getPaiementStatus($response);
```

**Variables `.env` :**
```env
FEEXPAY_SHOP_ID=<shop_id>
FEEXPAY_API_TOKEN=<token>
FEEXPAY_CALLBACK_URL=https://mondomaine.com/api/payment/feexpay/callback
FEEXPAY_MODE=SANDBOX
```

### Config `config/concours.php`

```php
'vote_price' => 100,          // FCFA par vote
'currency' => 'XOF',
'filieres' => ['DWM', 'PM', 'MMV', 'BTP', 'EA'],
'max_votes_per_transaction' => 100,
'payment_gateway' => env('PAYMENT_GATEWAY', 'pawapay'), // 'pawapay' ou 'feexpay'
'payment_simulation' => env('PAYMENT_SIMULATION', false),
```

## Architecture

### Structure des fichiers clés

```
app/
├── Http/Controllers/
│   ├── CandidatController.php    # CRUD candidats, filtrage par filière, stats
│   ├── VoteController.php        # Création vote, consultation, stats
│   └── PaymentController.php     # Initiation paiement, callbacks, simulation
├── Models/
│   ├── Candidat.php              # Scopes: filiere(), populaires()
│   ├── Vote.php                  # Boot hook: auto transaction_id, auto-incrémente total_votes
│   └── Transaction.php           # Référence paiement, réponse JSON
├── Services/                     # (à créer) Logique paiement découplée
│   ├── PawapayService.php        # Intégration PawaPay API v2
│   └── FeexpayService.php        # Intégration FeexPay SDK
bootstrap/
├── app.php                       # Config app Laravel 12 (routes, middleware, exceptions)
├── providers.php                 # Providers (AppServiceProvider uniquement)
config/concours.php               # Seul fichier config custom (les autres utilisent les defaults Laravel 12)
routes/api.php                    # Routes API publiques
routes/web.php                    # Pages web
public/css/app.css                # Styles (variables CSS, responsive)
public/js/app.js                  # Logique SPA
resources/views/
├── layouts/app.blade.php         # Layout maître
└── welcome.blade.php             # Page principale
```

### Modèles et relations

| Modèle | Relations | Champs clés |
|--------|-----------|-------------|
| `Candidat` | hasMany(Vote) | nom, prenom, filiere, photo_url, description, total_votes |
| `Vote` | belongsTo(Candidat), hasOne(Transaction) | nombre_votes, montant_total, transaction_id, statut_paiement (en_attente/reussi/echoue), ip_address |
| `Transaction` | belongsTo(Vote) | reference_externe, montant, statut, gateway (pawapay/feexpay), response_data (JSON) |

### Flux de paiement (mis à jour)

1. `POST /api/votes` → Crée un vote en attente
2. `POST /api/payment/initier` → Détecte la gateway active, initie le paiement via PawaPay ou FeexPay
3. `POST /api/payment/pawapay/callback` → Webhook PawaPay (statut COMPLETED/FAILED)
4. `POST /api/payment/feexpay/callback` → Webhook FeexPay
5. Dev : `GET /api/payment/simulate?vote_id=X&statut=reussi` (nécessite `PAYMENT_SIMULATION=true`)

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
POST   /api/payment/initier                    # Initier paiement
POST   /api/payment/pawapay/callback           # Webhook PawaPay
POST   /api/payment/feexpay/callback           # Webhook FeexPay
GET    /api/payment/simulate                   # Simulation (dev)
GET    /api/ping                               # Health check
```

## Conventions et règles

### Langue
- **Tout le contenu** visible par l'utilisateur est en **français** : labels, messages, notifications, commentaires
- Termes du domaine : candidat, filière, vote, reussi, echoue, en_attente

### Filières
- **DWM** (Développement Web et Mobile), **PM** (Plomberie), **MMV** (Menuiserie Métallique et Vitrerie), **BTP** (Bâtiment et Travaux Publics), **EA** (Électricité Automobile)

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
  - Vérifier les signatures des webhooks PawaPay (headers RFC-9421)
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
- **Double gateway** : Le `PaymentController` doit déléguer à `PawapayService` ou `FeexpayService` selon `config('concours.payment_gateway')`. Pattern Strategy recommandé.
- **Base de données** : SQLite en dev, PostgreSQL (Supabase) en prod. Pas de `unsigned()`, pas d'`ENUM` natif dans les migrations.
- **Secrets** : `.env` jamais commité. Clés PawaPay, FeexPay et Supabase dans `.env` uniquement.
- **Config** : Ne pas utiliser `url()` ou `route()` dans les fichiers `config/` (pas disponible au boot). Utiliser `env()` uniquement.
