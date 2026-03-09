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
        $this->moneroo = new MonerooPayment();
    }

    /**
     * Initier un paiement pour un vote
     */
    public function initierPaiementVote(Vote $vote): array
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
            'description' => "Vote pour {$vote->candidat->nom_complet} — {$vote->nombre_votes} vote(s)",
            'customer' => [
                'email' => 'votant@awards-ltpbopa.bj',
                'first_name' => 'Votant',
                'last_name' => 'Awards LTPBOPA',
                'phone' => $vote->telephone ?? '',
            ],
            'return_url' => url(config('concours.moneroo.return_url', '/paiement/retour')),
            'metadata' => $metadata,
        ];

        $response = $this->moneroo->init($paymentData);

        // Créer la transaction locale
        $transaction = Transaction::create([
            'vote_id' => $vote->id,
            'moneroo_id' => $response->id,
            'checkout_url' => $response->checkout_url,
            'montant' => $vote->montant_total,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_VOTE,
            'telephone' => $vote->telephone,
            'metadata' => $metadata,
        ]);

        Log::info('Paiement Moneroo initié (vote)', [
            'vote_id' => $vote->id,
            'moneroo_id' => $response->id,
            'montant' => $vote->montant_total,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'moneroo_id' => $response->id,
            'checkout_url' => $response->checkout_url,
        ];
    }

    /**
     * Initier un paiement pour un don
     */
    public function initierPaiementDon(Don $don): array
    {
        $metadata = [
            'type' => Transaction::TYPE_DON,
            'don_id' => (string) $don->id,
        ];

        $paymentData = [
            'amount' => $don->montant,
            'currency' => config('concours.currency', 'XOF'),
            'description' => "Don pour les Awards LTPBOPA — {$don->montant} FCFA",
            'customer' => [
                'email' => 'donateur@awards-ltpbopa.bj',
                'first_name' => $don->nom_donateur ?? 'Donateur',
                'last_name' => 'Anonyme',
                'phone' => $don->telephone,
            ],
            'return_url' => url(config('concours.moneroo.return_url', '/paiement/retour')),
            'metadata' => $metadata,
        ];

        $response = $this->moneroo->init($paymentData);

        $transaction = Transaction::create([
            'don_id' => $don->id,
            'moneroo_id' => $response->id,
            'checkout_url' => $response->checkout_url,
            'montant' => $don->montant,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_DON,
            'telephone' => $don->telephone,
            'metadata' => $metadata,
        ]);

        Log::info('Paiement Moneroo initié (don)', [
            'don_id' => $don->id,
            'moneroo_id' => $response->id,
            'montant' => $don->montant,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'moneroo_id' => $response->id,
            'checkout_url' => $response->checkout_url,
        ];
    }

    /**
     * Vérifier le statut d'un paiement via l'API Moneroo
     * Utilisé au retour utilisateur ET par la tâche planifiée
     */
    public function verifierPaiement(string $monerooId): bool
    {
        $transaction = Transaction::where('moneroo_id', $monerooId)->first();

        if (!$transaction) {
            Log::warning('Vérification Moneroo : transaction introuvable', ['moneroo_id' => $monerooId]);
            return false;
        }

        // Déjà traité ? On ne refait rien
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            return $transaction->statut === Transaction::STATUT_REUSSI;
        }

        $payment = $this->moneroo->verify($monerooId);
        $statutMoneroo = $payment->status ?? 'pending';

        // Statut pas encore final, on ne fait rien
        if ($statutMoneroo === 'pending' || $statutMoneroo === 'initiated') {
            return false;
        }

        return $this->traiterStatut($transaction, $statutMoneroo, (array) $payment);
    }

    /**
     * Traiter un webhook Moneroo
     */
    public function traiterWebhook(array $payload): bool
    {
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];
        $monerooId = $data['id'] ?? null;

        if (!$monerooId) {
            Log::warning('Webhook Moneroo : ID manquant', ['payload' => $payload]);
            return false;
        }

        $transaction = Transaction::where('moneroo_id', $monerooId)->first();

        if (!$transaction) {
            Log::warning('Webhook Moneroo : transaction introuvable', ['moneroo_id' => $monerooId]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            Log::info('Webhook Moneroo : transaction déjà traitée', [
                'moneroo_id' => $monerooId,
                'statut' => $transaction->statut,
            ]);
            return true;
        }

        $statutMoneroo = $data['status'] ?? match ($event) {
            'payment.success' => 'success',
            'payment.failed' => 'failed',
            'payment.cancelled' => 'cancelled',
            default => 'pending',
        };

        return $this->traiterStatut($transaction, $statutMoneroo, $data);
    }

    /**
     * Vérifier la signature du webhook
     */
    public function verifierSignature(string $payload, string $signature): bool
    {
        $secret = config('concours.moneroo.webhook_secret');

        if (!$secret) {
            Log::warning('Webhook Moneroo : secret non configuré');
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Vérifier toutes les transactions en attente (tâche planifiée)
     */
    public function reconcilierTransactionsEnAttente(): int
    {
        $transactions = Transaction::where('statut', Transaction::STATUT_EN_ATTENTE)
            ->where('created_at', '<', now()->subMinutes(3))
            ->whereNotNull('moneroo_id')
            ->oldest()
            ->limit(50)
            ->get();

        $traitees = 0;

        foreach ($transactions as $transaction) {
            try {
                $this->verifierPaiement($transaction->moneroo_id);
                $traitees++;
            } catch (\Exception $e) {
                Log::error('Réconciliation Moneroo : erreur', [
                    'moneroo_id' => $transaction->moneroo_id,
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
     * Logique commune : mettre à jour vote/don après confirmation de paiement
     */
    private function traiterStatut(Transaction $transaction, string $statutMoneroo, array $responseData): bool
    {
        $nouveauStatut = Transaction::mapStatutMoneroo($statutMoneroo);

        return DB::transaction(function () use ($transaction, $nouveauStatut, $responseData) {
            // Mettre à jour la transaction
            $transaction->update([
                'statut' => $nouveauStatut,
                'response_data' => $responseData,
                'processed_at' => now(),
            ]);

            // Mettre à jour le vote ou le don associé
            if ($transaction->type === Transaction::TYPE_VOTE && $transaction->vote_id) {
                $vote = Vote::find($transaction->vote_id);
                if ($vote && $vote->statut_paiement === Vote::STATUT_EN_ATTENTE) {
                    $vote->statut_paiement = $nouveauStatut === Transaction::STATUT_REUSSI
                        ? Vote::STATUT_REUSSI
                        : Vote::STATUT_ECHOUE;
                    $vote->save();
                }
            }

            if ($transaction->type === Transaction::TYPE_DON && $transaction->don_id) {
                $don = Don::find($transaction->don_id);
                if ($don && $don->statut === Don::STATUT_EN_ATTENTE) {
                    $don->statut = $nouveauStatut === Transaction::STATUT_REUSSI
                        ? Don::STATUT_REUSSI
                        : Don::STATUT_ECHOUE;
                    $don->save();
                }
            }

            Log::info('Paiement Moneroo traité', [
                'moneroo_id' => $transaction->moneroo_id,
                'type' => $transaction->type,
                'statut' => $nouveauStatut,
            ]);

            return $nouveauStatut === Transaction::STATUT_REUSSI;
        });
    }
}
