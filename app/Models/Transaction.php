<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     */
    protected $table = 'transactions';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'vote_id',
        'don_id',
        'deposit_id',
        'montant',
        'statut',
        'type',
        'telephone',
        'operateur',
        'gateway',
        'metadata',
        'response_data',
        'processed_at',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'vote_id' => 'integer',
        'don_id' => 'integer',
        'montant' => 'integer',
        'metadata' => 'array',
        'response_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Types de transaction
     */
    const TYPE_VOTE = 'vote';
    const TYPE_DON = 'don';

    /**
     * Statuts
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_REUSSI = 'reussi';
    const STATUT_ECHOUE = 'echoue';
    const STATUT_ANNULE = 'annule';

    /**
     * Gateway de paiement
     */
    const GATEWAY_MONEROO = 'moneroo';

    /**
     * Mapping statut Moneroo → statut interne
     */
    public static function mapStatutMoneroo(string $monerooStatus): string
    {
        return match (strtolower($monerooStatus)) {
            'success'   => self::STATUT_REUSSI,
            'failed'    => self::STATUT_ECHOUE,
            'cancelled' => self::STATUT_ANNULE,
            'pending', 'initiated' => self::STATUT_EN_ATTENTE,
            default     => self::STATUT_EN_ATTENTE,
        };
    }

    /**
     * Relation : Une transaction appartient à un vote
     */
    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    /**
     * Relation : Une transaction appartient à un don
     */
    public function don()
    {
        return $this->belongsTo(Don::class);
    }

    /**
     * Obtenir les statistiques globales des transactions
     */
    public static function getStatistiquesGlobales(): array
    {
        $total = self::count();
        $reussies = self::where('statut', self::STATUT_REUSSI)->count();
        $echouees = self::where('statut', self::STATUT_ECHOUE)->count();
        $montant = self::where('statut', self::STATUT_REUSSI)->sum('montant');

        return [
            'total_transactions' => $total,
            'transactions_reussies' => $reussies,
            'transactions_echouees' => $echouees,
            'total_montant' => $montant,
            'taux_reussite' => $total > 0 ? round(($reussies / $total) * 100, 2) : 0,
        ];
    }
}
