<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Don extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     */
    protected $table = 'dons';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'transaction_id',
        'nom_donateur',
        'telephone',
        'montant',
        'statut',
        'message',
        'ip_address',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'montant' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Statuts possibles
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_REUSSI = 'reussi';
    const STATUT_ECHOUE = 'echoue';

    /**
     * Relation : Un don a une transaction
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Scope : Dons réussis
     */
    public function scopeReussis($query)
    {
        return $query->where('statut', self::STATUT_REUSSI);
    }

    /**
     * Scope : Dons en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    /**
     * Marquer le don comme réussi
     */
    public function marquerReussi(): bool
    {
        $this->statut = self::STATUT_REUSSI;
        return $this->save();
    }

    /**
     * Marquer le don comme échoué
     */
    public function marquerEchoue(): bool
    {
        $this->statut = self::STATUT_ECHOUE;
        return $this->save();
    }

    /**
     * Générer un ID de transaction unique
     */
    public static function genererTransactionId(): string
    {
        return 'DON-' . uniqid() . '-' . time();
    }

    /**
     * Statistiques globales des dons
     */
    public static function getStatistiques(): array
    {
        return [
            'total_dons' => self::reussis()->count(),
            'montant_total' => self::reussis()->sum('montant'),
            'don_moyen' => (int) self::reussis()->avg('montant'),
        ];
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($don) {
            if (!$don->transaction_id) {
                $don->transaction_id = self::genererTransactionId();
            }
            if (!$don->ip_address) {
                $don->ip_address = request()->ip();
            }
        });
    }
}
