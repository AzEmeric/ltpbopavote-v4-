<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     */
    protected $table = 'votes';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'candidat_id',
        'nombre_votes',
        'montant_total',
        'transaction_id',
        'telephone',
        'statut_paiement',
        'ip_address',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'candidat_id' => 'integer',
        'nombre_votes' => 'integer',
        'montant_total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Statuts de paiement possibles
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_REUSSI = 'reussi';
    const STATUT_ECHOUE = 'echoue';

    /**
     * Relation : Un vote appartient à un candidat
     */
    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }

    /**
     * Relation : Un vote a une transaction
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Scope : Votes en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut_paiement', self::STATUT_EN_ATTENTE);
    }

    /**
     * Scope : Votes réussis
     */
    public function scopeReussis($query)
    {
        return $query->where('statut_paiement', self::STATUT_REUSSI);
    }

    /**
     * Scope : Votes échoués
     */
    public function scopeEchoues($query)
    {
        return $query->where('statut_paiement', self::STATUT_ECHOUE);
    }

    /**
     * Vérifier si le vote est en attente
     */
    public function estEnAttente(): bool
    {
        return $this->statut_paiement === self::STATUT_EN_ATTENTE;
    }

    /**
     * Vérifier si le vote est réussi
     */
    public function estReussi(): bool
    {
        return $this->statut_paiement === self::STATUT_REUSSI;
    }

    /**
     * Vérifier si le vote est échoué
     */
    public function estEchoue(): bool
    {
        return $this->statut_paiement === self::STATUT_ECHOUE;
    }

    /**
     * Marquer le vote comme réussi
     */
    public function marquerReussi(string $transactionId = null): bool
    {
        $this->statut_paiement = self::STATUT_REUSSI;
        
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        
        return $this->save();
    }

    /**
     * Marquer le vote comme échoué
     */
    public function marquerEchoue(): bool
    {
        $this->statut_paiement = self::STATUT_ECHOUE;
        return $this->save();
    }

    /**
     * Générer un ID de transaction unique
     */
    public static function genererTransactionId(): string
    {
        return 'TXN-' . uniqid() . '-' . time();
    }

    /**
     * Valider le montant
     */
    public function validerMontant(float $prixUnitaire): bool
    {
        $montantAttendu = $this->nombre_votes * $prixUnitaire;
        return abs($this->montant_total - $montantAttendu) < 0.01; // Tolérance de 1 centime
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création, générer un transaction_id si absent
        static::creating(function ($vote) {
            if (!$vote->transaction_id) {
                $vote->transaction_id = self::genererTransactionId();
            }

            // Capturer l'IP si absente
            if (!$vote->ip_address) {
                $vote->ip_address = request()->ip();
            }
        });

        // Après la mise à jour vers "réussi", incrémenter les votes du candidat
        static::updated(function ($vote) {
            if ($vote->isDirty('statut_paiement') && $vote->statut_paiement === self::STATUT_REUSSI) {
                $vote->candidat->incrementVotes($vote->nombre_votes);
            }
        });
    }
}
