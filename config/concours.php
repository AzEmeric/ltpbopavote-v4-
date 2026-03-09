<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration du Concours
    |--------------------------------------------------------------------------
    */

    // Prix d'un vote en FCFA
    'vote_price' => 100,

    // Devise
    'currency' => 'XOF',

    /*
    |--------------------------------------------------------------------------
    | Filières disponibles
    |--------------------------------------------------------------------------
    */

    'filieres' => [
        'DWM' => 'Développement Web et Mobile',
        'PM'  => 'Producteur Multimédia',
        'MMV' => 'Métier de la Mode et Vêtement',
        'BTP' => 'Bâtiment et Travaux Publics',
        'EA'  => 'Électronique Appliquée',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites et restrictions
    |--------------------------------------------------------------------------
    */

    // Nombre maximum de votes par transaction
    'max_votes_per_transaction' => 100,

    // Nombre minimum de votes par transaction
    'min_votes_per_transaction' => 1,

    // Montant minimum pour un don (FCFA)
    'min_don' => 100,

    /*
    |--------------------------------------------------------------------------
    | Moneroo — Passerelle de paiement unique
    |--------------------------------------------------------------------------
    |
    | Doc : https://docs.moneroo.io
    | SDK Laravel : composer require moneroo/moneroo-laravel
    | API Base URL : https://api.moneroo.io/v1
    | Auth : Bearer token (Secret Key)
    |
    | Webhooks events :
    |   - payment.initiated, payment.success, payment.failed, payment.cancelled
    | Signature : X-Moneroo-Signature (HMAC-SHA256 avec webhook secret)
    |
    */

    'moneroo' => [
        'secret_key'     => env('MONEROO_SECRET_KEY'),
        'public_key'     => env('MONEROO_PUBLIC_KEY'),
        'webhook_secret' => env('MONEROO_WEBHOOK_SECRET'),
        'return_url'     => env('MONEROO_RETURN_URL', '/paiement/retour'),
    ],

    // Activer la simulation de paiement (développement uniquement)
    'payment_simulation' => env('PAYMENT_SIMULATION', false),

    /*
    |--------------------------------------------------------------------------
    | Upload de photos
    |--------------------------------------------------------------------------
    */

    // Chemin des photos de candidats
    'candidat_photos_path' => 'uploads/candidats',

    // Photo par défaut
    'default_photo' => 'uploads/candidats/default.jpg',

    // Taille maximale des photos (en Ko)
    'max_photo_size' => 2048, // 2 MB

    // Types de fichiers autorisés
    'allowed_photo_types' => ['jpg', 'jpeg', 'png', 'webp'],

];
