<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\Don;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Moneroo\Payment as MonerooPayment;

class MonerooService
{
    private MonerooPayment $moneroo;

    public function __construct()
    {
        $secretKey = config('concours.moneroo.secret_key', '');
        $this->moneroo = new MonerooPayment($secretKey);
    }

    /**
     * Initier un paiement pour un vote (redirection checkout Moneroo)
     */
    public function initierPaiementVote(Vote $vote, ?string $telephone = null): array
    {
        $vote->load('candidat');

        $metadata = [
            'type' => Transaction::TYPE_VOTE,
            'vote_id' => (string) $vote->id,
            'candidat_id' => (string) $vote->candidat_id,
            'nombre_votes' => (string) $vote->nombre_votes,
        ];

        $paymentData = [
            'amount' => $vote->montant_total,
            'currency' => config('concours.currency', 'XOF'),
            'description' => "Vote pour {$vote->candidat->prenom} {$vote->candidat->nom} - {$vote->nombre_votes} vote(s)",
            'return_url' => url('/paiement/retour'),
            'customer' => [
                'email' => 'votant@ltpbopa.bj',
                'first_name' => 'Votant',
                'last_name' => 'LTP-BOPA',
                'phone' => $telephone ? $this->formaterTelephone($telephone) : '',
            ],
            'metadata' => $metadata,
        ];

        $payment = $this->moneroo->init($paymentData);

        $paymentId = $payment->id ?? null;
        $checkoutUrl = $payment->checkout_url ?? null;

        if (!$paymentId || !$checkoutUrl) {
            throw new \RuntimeException("Erreur Moneroo : impossible d'initier le paiement");
        }

        // Créer la transaction locale
        $transaction = Transaction::create([
            'vote_id' => $vote->id,
            'deposit_id' => $paymentId,
            'montant' => $vote->montant_total,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_VOTE,
            'telephone' => $telephone ?? '',
            'gateway' => Transaction::GATEWAY_MONEROO,
            'metadata' => $metadata,
            'response_data' => (array) $payment,
        ]);

        Log::info('Paiement Moneroo initié (vote)', [
            'vote_id' => $vote->id,
            'payment_id' => $paymentId,
            'montant' => $vote->montant_total,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'payment_id' => $paymentId,
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Initier un paiement pour un don (redirection checkout Moneroo)
     */
    public function initierPaiementDon(Don $don, ?string $telephone = null): array
    {
        $metadata = [
            'type' => Transaction::TYPE_DON,
            'don_id' => (string) $don->id,
        ];

        $paymentData = [
            'amount' => $don->montant,
            'currency' => config('concours.currency', 'XOF'),
            'description' => "Don de {$don->montant} FCFA - LTP-BOPA",
            'return_url' => url('/paiement/retour'),
            'customer' => [
                'email' => 'donateur@ltpbopa.bj',
                'first_name' => $don->nom_donateur ?: 'Donateur',
                'last_name' => 'Anonyme',
                'phone' => $telephone ? $this->formaterTelephone($telephone) : '',
            ],
            'metadata' => $metadata,
        ];

        $payment = $this->moneroo->init($paymentData);

        $paymentId = $payment->id ?? null;
        $checkoutUrl = $payment->checkout_url ?? null;

        if (!$paymentId || !$checkoutUrl) {
            throw new \RuntimeException("Erreur Moneroo : impossible d'initier le paiement");
        }

        $transaction = Transaction::create([
            'don_id' => $don->id,
            'deposit_id' => $paymentId,
            'montant' => $don->montant,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_DON,
            'telephone' => $telephone ?? '',
            'gateway' => Transaction::GATEWAY_MONEROO,
            'metadata' => $metadata,
            'response_data' => (array) $payment,
        ]);

        Log::info('Paiement Moneroo initié (don)', [
            'don_id' => $don->id,
            'payment_id' => $paymentId,
            'montant' => $don->montant,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'payment_id' => $paymentId,
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Vérifier le statut d'un paiement via l'API Moneroo
     */
    public function verifierPaiement(string $paymentId): bool
    {
        $transaction = Transaction::where('deposit_id', $paymentId)->first();

        if (!$transaction) {
            Log::warning('Vérification Moneroo : transaction introuvable', ['payment_id' => $paymentId]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            return $transaction->statut === Transaction::STATUT_REUSSI;
        }

        try {
            $payment = $this->moneroo->verify($paymentId);
            $statutMoneroo = $payment->status ?? 'pending';

            Log::info('Vérification Moneroo : statut reçu', [
                'payment_id' => $paymentId,
                'statut_moneroo' => $statutMoneroo,
            ]);

            // Statut pas encore final
            if ($statutMoneroo === 'pending' || $statutMoneroo === 'initiated') {
                return false;
            }

            return $this->traiterStatut($transaction, $statutMoneroo, (array) $payment);
        } catch (\Exception $e) {
            Log::error('Vérification Moneroo : erreur', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Traiter le retour de Moneroo (page de retour après paiement)
     */
    public function traiterRetour(string $paymentId): array
    {
        $transaction = Transaction::where('deposit_id', $paymentId)->first();

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction introuvable',
            ];
        }

        // Si déjà traité, retourner le statut
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            return [
                'success' => true,
                'statut' => $transaction->statut,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'paiement_reussi' => $transaction->statut === Transaction::STATUT_REUSSI,
            ];
        }

        // Vérifier via l'API Moneroo
        $this->verifierPaiement($paymentId);
        $transaction->refresh();

        return [
            'success' => true,
            'statut' => $transaction->statut,
            'type' => $transaction->type,
            'montant' => $transaction->montant,
            'paiement_reussi' => $transaction->statut === Transaction::STATUT_REUSSI,
        ];
    }

    /**
     * Vérifier toutes les transactions en attente (tâche planifiée)
     */
    public function reconcilierTransactionsEnAttente(): int
    {
        $transactions = Transaction::where('statut', Transaction::STATUT_EN_ATTENTE)
            ->where('created_at', '<', now()->subMinutes(3))
            ->whereNotNull('deposit_id')
            ->oldest()
            ->limit(50)
            ->get();

        $traitees = 0;

        foreach ($transactions as $transaction) {
            try {
                $this->verifierPaiement($transaction->deposit_id);
                $traitees++;
            } catch (\Exception $e) {
                Log::error('Réconciliation Moneroo : erreur', [
                    'payment_id' => $transaction->deposit_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($traitees > 0) {
            Log::info("Réconciliation Moneroo : {$traitees} transactions vérifiées");
        }

        return $traitees;
    }

    /**
     * Rechercher les votes/dons par numéro de téléphone
     */
    public function rechercherParTelephone(string $telephone): array
    {
        // Vérifier les votes en attente auprès de l'API avant de retourner les résultats
        $votesEnAttente = Vote::where('telephone', $telephone)
            ->where('statut_paiement', Vote::STATUT_EN_ATTENTE)
            ->with('transaction')
            ->get();

        foreach ($votesEnAttente as $vote) {
            if ($vote->transaction && $vote->transaction->deposit_id) {
                try {
                    $this->verifierPaiement($vote->transaction->deposit_id);
                } catch (\Exception $e) {
                    Log::warning('Erreur vérification vote en attente', [
                        'vote_id' => $vote->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $votes = Vote::where('telephone', $telephone)
            ->with('candidat')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Vote $v) => [
                'type' => 'vote',
                'id' => $v->id,
                'transaction_id' => $v->transaction_id,
                'candidat' => $v->candidat->nom_complet ?? 'Inconnu',
                'candidat_filiere' => $v->candidat->filiere ?? '',
                'candidat_photo' => $v->candidat->photo_url_complete ?? '',
                'candidat_total_votes' => $v->candidat->total_votes ?? 0,
                'nombre_votes' => $v->nombre_votes,
                'montant' => $v->montant_total,
                'statut' => $v->statut_paiement,
                'date' => $v->created_at->format('d/m/Y H:i'),
            ]);

        $dons = Don::where('telephone', $telephone)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Don $d) => [
                'type' => 'don',
                'id' => $d->id,
                'transaction_id' => $d->transaction_id,
                'montant' => $d->montant,
                'statut' => $d->statut,
                'date' => $d->created_at->format('d/m/Y H:i'),
            ]);

        return [
            'votes' => $votes,
            'dons' => $dons,
        ];
    }

    /**
     * Formater le numéro de téléphone au format international (229XXXXXXXX)
     */
    private function formaterTelephone(string $telephone): string
    {
        $tel = preg_replace('/[^0-9]/', '', $telephone);

        if (!str_starts_with($tel, '229')) {
            $tel = '229' . $tel;
        }

        return $tel;
    }

    /**
     * Logique commune : mettre à jour vote/don après confirmation de paiement
     */
    private function traiterStatut(Transaction $transaction, string $statutMoneroo, array $responseData): bool
    {
        $nouveauStatut = Transaction::mapStatutMoneroo($statutMoneroo);

        // Reconnexion préventive (Neon connection pooler)
        try {
            DB::reconnect();
        } catch (\Exception $e) {
            Log::warning('DB::reconnect() échoué, on continue', ['error' => $e->getMessage()]);
        }

        // Sauvegarder les données de réponse sur la transaction (sans changer le statut encore)
        $transaction->update([
            'response_data' => $responseData,
        ]);

        if ($transaction->type === Transaction::TYPE_VOTE && $transaction->vote_id) {
            $vote = Vote::find($transaction->vote_id);
            if ($vote && $vote->statut_paiement === Vote::STATUT_EN_ATTENTE) {
                $paiementReussi = $nouveauStatut === Transaction::STATUT_REUSSI;

                if ($paiementReussi) {
                    // 1. Incrémenter les votes du candidat EN PREMIER
                    try {
                        $candidat = $vote->candidat;
                        if ($candidat) {
                            $candidat->incrementVotes($vote->nombre_votes);
                        }
                    } catch (\Exception $e) {
                        // L'incrément a échoué : ne pas marquer le vote/transaction
                        // comme réussi. La réconciliation retentera dans 30s.
                        Log::error('Échec incrémentation votes — transaction reste en attente', [
                            'vote_id' => $vote->id,
                            'candidat_id' => $vote->candidat_id,
                            'error' => $e->getMessage(),
                        ]);
                        return false;
                    }

                    // 2. Seulement après succès de l'incrément, marquer le vote et la transaction
                    $vote->statut_paiement = Vote::STATUT_REUSSI;
                    $vote->saveQuietly();
                    $transaction->update(['statut' => $nouveauStatut, 'processed_at' => now()]);

                    Log::info('Votes incrémentés et vote confirmé', [
                        'vote_id' => $vote->id,
                        'candidat_id' => $vote->candidat_id,
                        'nombre_votes' => $vote->nombre_votes,
                    ]);
                } else {
                    // Paiement échoué : marquer directement
                    $vote->statut_paiement = Vote::STATUT_ECHOUE;
                    $vote->saveQuietly();
                    $transaction->update(['statut' => $nouveauStatut, 'processed_at' => now()]);
                }
            } else {
                // Vote déjà traité, juste mettre à jour la transaction
                $transaction->update(['statut' => $nouveauStatut, 'processed_at' => now()]);
            }
        } elseif ($transaction->type === Transaction::TYPE_DON && $transaction->don_id) {
            $don = Don::find($transaction->don_id);
            if ($don && $don->statut === Don::STATUT_EN_ATTENTE) {
                $don->statut = $nouveauStatut === Transaction::STATUT_REUSSI
                    ? Don::STATUT_REUSSI
                    : Don::STATUT_ECHOUE;
                $don->save();
            }
            $transaction->update(['statut' => $nouveauStatut, 'processed_at' => now()]);
        } else {
            $transaction->update(['statut' => $nouveauStatut, 'processed_at' => now()]);
        }

        Log::info('Paiement Moneroo traité', [
            'payment_id' => $transaction->deposit_id,
            'type' => $transaction->type,
            'statut' => $nouveauStatut,
        ]);

        return $nouveauStatut === Transaction::STATUT_REUSSI;
    }
}
