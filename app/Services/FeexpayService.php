<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\Don;
use App\Models\Transaction;
use Feexpay\FeexpayPhp\FeexpayClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeexpayService
{
    private ?string $shopId;
    private ?string $apiToken;
    private ?string $callbackUrl;
    private string $mode;

    /**
     * Mapping opérateur simplifié → nom FeexPay
     */
    private const OPERATEURS = [
        'MTN'  => 'MTN',
        'MOOV' => 'MOOV',
    ];

    public function __construct()
    {
        $this->shopId = config('concours.feexpay.shop_id');
        $this->apiToken = config('concours.feexpay.api_token');
        $this->callbackUrl = config('concours.feexpay.callback_url');
        $this->mode = config('concours.feexpay.mode') ?? 'SANDBOX';
    }

    /**
     * Initier un paiement pour un vote via FeexPay
     */
    public function initierPaiementVote(Vote $vote, string $telephone, string $operateur): array
    {
        $vote->load('candidat');

        $depositId = (string) Str::uuid();

        $metadata = [
            'type' => Transaction::TYPE_VOTE,
            'vote_id' => (string) $vote->id,
            'candidat_id' => (string) $vote->candidat_id,
            'nombre_votes' => (string) $vote->nombre_votes,
        ];

        $provider = self::OPERATEURS[$operateur] ?? null;
        if (!$provider) {
            throw new \InvalidArgumentException("Opérateur non supporté : {$operateur}");
        }

        $reference = $this->envoyerPaiement($telephone, $provider, $vote->montant_total);

        if (!$reference) {
            throw new \RuntimeException("Erreur FeexPay : impossible d'initier le paiement");
        }

        $transaction = Transaction::create([
            'vote_id' => $vote->id,
            'deposit_id' => $reference,
            'montant' => $vote->montant_total,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_VOTE,
            'telephone' => $telephone,
            'operateur' => $operateur,
            'gateway' => Transaction::GATEWAY_FEEXPAY,
            'metadata' => $metadata,
            'response_data' => ['reference' => $reference],
        ]);

        Log::info('Paiement FeexPay initié (vote)', [
            'vote_id' => $vote->id,
            'reference' => $reference,
            'montant' => $vote->montant_total,
            'operateur' => $operateur,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'deposit_id' => $reference,
        ];
    }

    /**
     * Initier un paiement pour un don via FeexPay
     */
    public function initierPaiementDon(Don $don, string $telephone, string $operateur): array
    {
        $depositId = (string) Str::uuid();

        $metadata = [
            'type' => Transaction::TYPE_DON,
            'don_id' => (string) $don->id,
        ];

        $provider = self::OPERATEURS[$operateur] ?? null;
        if (!$provider) {
            throw new \InvalidArgumentException("Opérateur non supporté : {$operateur}");
        }

        $reference = $this->envoyerPaiement($telephone, $provider, $don->montant);

        if (!$reference) {
            throw new \RuntimeException("Erreur FeexPay : impossible d'initier le paiement");
        }

        $transaction = Transaction::create([
            'don_id' => $don->id,
            'deposit_id' => $reference,
            'montant' => $don->montant,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_DON,
            'telephone' => $telephone,
            'operateur' => $operateur,
            'gateway' => Transaction::GATEWAY_FEEXPAY,
            'metadata' => $metadata,
            'response_data' => ['reference' => $reference],
        ]);

        Log::info('Paiement FeexPay initié (don)', [
            'don_id' => $don->id,
            'reference' => $reference,
            'montant' => $don->montant,
            'operateur' => $operateur,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'deposit_id' => $reference,
        ];
    }

    /**
     * Vérifier le statut d'un paiement via l'API FeexPay
     */
    public function verifierPaiement(string $reference): bool
    {
        $transaction = Transaction::where('deposit_id', $reference)->first();

        if (!$transaction) {
            Log::warning('Vérification FeexPay : transaction introuvable', ['reference' => $reference]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            return $transaction->statut === Transaction::STATUT_REUSSI;
        }

        try {
            $feexpay = $this->creerClient();
            $result = $feexpay->getPaiementStatus($reference);

            if (!$result || !isset($result['status'])) {
                return false;
            }

            $statutFeexpay = $result['status'];

            // Statut pas encore final
            if ($statutFeexpay === 'PENDING') {
                return false;
            }

            return $this->traiterStatut($transaction, $statutFeexpay, $result);
        } catch (\Exception $e) {
            Log::error('Vérification FeexPay : erreur', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Traiter un webhook FeexPay
     */
    public function traiterWebhook(array $payload): bool
    {
        $reference = $payload['reference'] ?? $payload['transref'] ?? null;
        $statutFeexpay = $payload['status'] ?? null;

        if (!$reference) {
            Log::warning('Webhook FeexPay : reference manquante', ['payload' => $payload]);
            return false;
        }

        $transaction = Transaction::where('deposit_id', $reference)->first();

        if (!$transaction) {
            Log::warning('Webhook FeexPay : transaction introuvable', ['reference' => $reference]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            Log::info('Webhook FeexPay : transaction déjà traitée', [
                'reference' => $reference,
                'statut' => $transaction->statut,
            ]);
            return true;
        }

        return $this->traiterStatut($transaction, $statutFeexpay, $payload);
    }

    /**
     * Vérifier toutes les transactions FeexPay en attente (tâche planifiée)
     */
    public function reconcilierTransactionsEnAttente(): int
    {
        $transactions = Transaction::where('statut', Transaction::STATUT_EN_ATTENTE)
            ->where('gateway', Transaction::GATEWAY_FEEXPAY)
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
                Log::error('Réconciliation FeexPay : erreur', [
                    'reference' => $transaction->deposit_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($traitees > 0) {
            Log::info("Réconciliation FeexPay : {$traitees} transactions vérifiées");
        }

        return $traitees;
    }

    /**
     * Envoyer un paiement local via le SDK FeexPay
     */
    private function envoyerPaiement(string $telephone, string $operateur, int $montant): ?string
    {
        $feexpay = $this->creerClient();

        $tel = $this->formaterTelephone($telephone);

        try {
            $reference = $feexpay->paiementLocal(
                (float) $montant,
                $tel,
                $operateur,
                'Votant LTP-BOPA',
                'vote@ltpbopa.bj'
            );

            if ($reference === false) {
                Log::error('FeexPay paiement échoué : shop ID invalide');
                return null;
            }

            return $reference;
        } catch (\Exception $e) {
            Log::error('FeexPay paiement échoué', [
                'error' => $e->getMessage(),
                'telephone' => $tel,
                'montant' => $montant,
            ]);
            throw new \RuntimeException("Erreur FeexPay : {$e->getMessage()}");
        }
    }

    /**
     * Créer une instance du client FeexPay
     */
    private function creerClient(): FeexpayClass
    {
        if (!$this->shopId || !$this->apiToken) {
            throw new \RuntimeException('FeexPay non configuré : FEEXPAY_SHOP_ID et FEEXPAY_API_TOKEN requis dans .env');
        }

        return new FeexpayClass(
            $this->shopId,
            $this->apiToken,
            $this->callbackUrl ?? '',
            $this->mode
        );
    }

    /**
     * Formater le numéro de téléphone (format local sans préfixe pays)
     */
    private function formaterTelephone(string $telephone): string
    {
        $tel = preg_replace('/[^0-9]/', '', $telephone);

        // Retirer le préfixe Bénin si présent (FeexPay utilise le format local)
        if (str_starts_with($tel, '229') && strlen($tel) > 10) {
            $tel = substr($tel, 3);
        }

        return $tel;
    }

    /**
     * Mapping statut FeexPay → statut interne
     */
    public static function mapStatutFeexpay(string $feexpayStatus): string
    {
        return match (strtoupper($feexpayStatus)) {
            'SUCCESSFUL', 'SUCCESS', 'COMPLETED' => Transaction::STATUT_REUSSI,
            'FAILED', 'REJECTED'                  => Transaction::STATUT_ECHOUE,
            'CANCELLED'                            => Transaction::STATUT_ANNULE,
            default                                => Transaction::STATUT_EN_ATTENTE,
        };
    }

    /**
     * Logique commune : mettre à jour vote/don après confirmation de paiement
     */
    private function traiterStatut(Transaction $transaction, string $statutFeexpay, array $responseData): bool
    {
        $nouveauStatut = self::mapStatutFeexpay($statutFeexpay);

        return DB::transaction(function () use ($transaction, $nouveauStatut, $responseData) {
            $transaction->update([
                'statut' => $nouveauStatut,
                'response_data' => $responseData,
                'processed_at' => now(),
            ]);

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

            Log::info('Paiement FeexPay traité', [
                'reference' => $transaction->deposit_id,
                'type' => $transaction->type,
                'statut' => $nouveauStatut,
            ]);

            return $nouveauStatut === Transaction::STATUT_REUSSI;
        });
    }
}
