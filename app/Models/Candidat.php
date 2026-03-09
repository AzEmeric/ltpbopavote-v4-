<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     */
    protected $table = 'candidats';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'filiere',
        'photo_url',
        'description',
        'total_votes',
        'actif',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation.
     */
    protected $hidden = [];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'total_votes' => 'integer',
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les attributs ajoutés à la sérialisation.
     */
    protected $appends = [
        'nom_complet',
        'photo_url_complete'
    ];

    /**
     * Relation : Un candidat a plusieurs votes
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Relation : Votes réussis uniquement
     */
    public function votesReussis()
    {
        return $this->hasMany(Vote::class)->where('statut_paiement', 'reussi');
    }

    /**
     * Accessor : Nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Accessor : URL complète de la photo
     */
    public function getPhotoUrlCompleteAttribute(): string
    {
        if (!$this->photo_url) {
            return asset('uploads/candidats/default.jpg');
        }

        // Si l'URL est déjà complète (http/https)
        if (str_starts_with($this->photo_url, 'http')) {
            return $this->photo_url;
        }

        // Sinon, construire l'URL complète
        return asset($this->photo_url);
    }

    /**
     * Scope : Filtrer par filière
     */
    public function scopeFiliere($query, $filiere)
    {
        return $query->where('filiere', $filiere);
    }

    /**
     * Scope : Trier par nombre de votes
     */
    public function scopePopulaires($query)
    {
        return $query->orderBy('total_votes', 'desc');
    }

    /**
     * Scope : Candidats actifs uniquement
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Incrémenter le nombre de votes
     */
    public function incrementVotes(int $nombre): bool
    {
        return $this->increment('total_votes', $nombre);
    }

    /**
     * Obtenir le nombre total de votes pour une filière
     */
    public static function getTotalVotesParFiliere(string $filiere): int
    {
        return self::where('filiere', $filiere)->sum('total_votes');
    }

    /**
     * Obtenir les statistiques d'un candidat
     */
    public function getStatistiques(): array
    {
        $total_votes = $this->total_votes;
        $total_transactions = $this->votesReussis()->count();
        $montant_collecte = $this->votesReussis()->sum('montant_total');
        $dernier_vote = $this->votesReussis()->latest()->first();

        return [
            'total_votes' => $total_votes,
            'total_transactions' => $total_transactions,
            'montant_collecte' => $montant_collecte,
            'dernier_vote' => $dernier_vote ? $dernier_vote->created_at : null,
        ];
    }
}
