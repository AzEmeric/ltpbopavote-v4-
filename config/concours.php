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
        'TEA' => 'Technicien en Électronique Appliquée',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites et restrictions
    |--------------------------------------------------------------------------
    */

    // Date et heure de clôture des votes (format ISO 8601, fuseau Bénin WAT +01:00)
    'vote_deadline' => env('VOTE_DEADLINE', '2026-03-23T23:59:00+01:00'),

    // Nombre maximum de votes par transaction
    'max_votes_per_transaction' => 900,

    // Nombre minimum de votes par transaction
    'min_votes_per_transaction' => 1,

    // Montant minimum pour un don (FCFA)
    'min_don' => 100,

    /*
    |--------------------------------------------------------------------------
    | Moneroo — Passerelle de paiement (redirection checkout)
    |--------------------------------------------------------------------------
    |
    | Doc : https://docs.moneroo.io
    | SDK PHP : moneroo/moneroo-php
    | Flow : redirection vers page checkout Moneroo, retour via return_url
    |
    */

    'moneroo' => [
        'secret_key' => env('MONEROO_SECRET_KEY'),
        'webhook_secret' => env('MONEROO_WEBHOOK_SECRET'),
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
