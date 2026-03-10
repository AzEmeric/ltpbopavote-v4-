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
    | PawaPay — Passerelle de paiement (STK Push)
    |--------------------------------------------------------------------------
    |
    | Doc : https://docs.pawapay.io
    | API Base URL : Production https://api.pawapay.io/ | Sandbox https://api.sandbox.pawapay.io/
    | Auth : Bearer token (API Token)
    | Bénin : MTN_MOMO_BEN, MOOV_BEN — devise XOF (pas de décimales)
    |
    | Webhook : POST callback avec headers Content-Digest, Signature, Signature-Input (RFC-9421)
    | Statuts : COMPLETED, FAILED, REJECTED, CANCELLED
    |
    */

    'pawapay' => [
        'api_token'    => env('PAWAPAY_API_TOKEN'),
        'base_url'     => env('PAWAPAY_BASE_URL', 'https://api.sandbox.pawapay.io'),
        'callback_url' => env('PAWAPAY_CALLBACK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | FeexPay — Passerelle de paiement alternative
    |--------------------------------------------------------------------------
    |
    | Doc : https://feexpay.me
    | API : https://api.feexpay.me
    | Paiement local Mobile Money (MTN, MOOV) via SDK PHP
    |
    */

    'feexpay' => [
        'shop_id'      => env('FEEXPAY_SHOP_ID'),
        'api_token'    => env('FEEXPAY_API_TOKEN'),
        'callback_url' => env('FEEXPAY_CALLBACK_URL'),
        'mode'         => env('FEEXPAY_MODE', 'SANDBOX'),
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
