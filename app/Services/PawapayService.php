<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\Don;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PawapayService
{
    private string $baseUrl;
    private string $apiToken;

    /**
     * Mapping opérateur simplifié → code PawaPay Bénin
     */
    private const OPERATEURS = [
        'MTN'  => 'MTN_MOMO_BEN',
        'MOOV' => 'MOOV_BEN',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('concours.pawapay.base_url', 'https://api.sandbox.pawapay.io'), '/');
        $this->apiToken = config('concours.pawapay.api_token', '');
    }

    /**
     * Initier un paiement pour un vote (STK Push)
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

        $response = $this->envoyerDepot($depositId, $telephone, $provider, $vote->montant_total, $vote->transaction_id);

        // Créer la transaction locale
        $transaction = Transaction::create([
            'vote_id' => $vote->id,
            'deposit_id' => $depositId,
            'montant' => $vote->montant_total,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_VOTE,
            'telephone' => $telephone,
            'operateur' => $operateur,
            'gateway' => Transaction::GATEWAY_PAWAPAY,
            'metadata' => $metadata,
            'response_data' => $response,
        ]);

        Log::info('Paiement PawaPay initié (vote)', [
            'vote_id' => $vote->id,
            'deposit_id' => $depositId,
            'montant' => $vote->montant_total,
            'operateur' => $operateur,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'deposit_id' => $depositId,
        ];
    }

    /**
     * Initier un paiement pour un don (STK Push)
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

        $response = $this->envoyerDepot($depositId, $telephone, $provider, $don->montant, $don->transaction_id);

        $transaction = Transaction::create([
            'don_id' => $don->id,
            'deposit_id' => $depositId,
            'montant' => $don->montant,
            'statut' => Transaction::STATUT_EN_ATTENTE,
            'type' => Transaction::TYPE_DON,
            'telephone' => $telephone,
            'operateur' => $operateur,
            'gateway' => Transaction::GATEWAY_PAWAPAY,
            'metadata' => $metadata,
            'response_data' => $response,
        ]);

        Log::info('Paiement PawaPay initié (don)', [
            'don_id' => $don->id,
            'deposit_id' => $depositId,
            'montant' => $don->montant,
            'operateur' => $operateur,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'deposit_id' => $depositId,
        ];
    }

    /**
     * Vérifier le statut d'un dépôt via l'API PawaPay
     */
    public function verifierPaiement(string $depositId): bool
    {
        $transaction = Transaction::where('deposit_id', $depositId)->first();

        if (!$transaction) {
            Log::warning('Vérification PawaPay : transaction introuvable', ['deposit_id' => $depositId]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            return $transaction->statut === Transaction::STATUT_REUSSI;
        }

        $response = Http::withToken($this->apiToken)
            ->get("{$this->baseUrl}/v2/deposits/{$depositId}");

        if (!$response->successful()) {
            Log::error('Vérification PawaPay : erreur API', [
                'deposit_id' => $depositId,
                'status' => $response->status(),
            ]);
            return false;
        }

        $data = $response->json();
        $statutPawapay = $data['status'] ?? 'SUBMITTED';

        // Statut pas encore final
        if (in_array($statutPawapay, ['SUBMITTED', 'ACCEPTED'])) {
            return false;
        }

        return $this->traiterStatut($transaction, $statutPawapay, $data);
    }

    /**
     * Traiter un webhook PawaPay
     */
    public function traiterWebhook(array $payload): bool
    {
        $depositId = $payload['depositId'] ?? null;
        $statutPawapay = $payload['status'] ?? null;

        if (!$depositId) {
            Log::warning('Webhook PawaPay : depositId manquant', ['payload' => $payload]);
            return false;
        }

        $transaction = Transaction::where('deposit_id', $depositId)->first();

        if (!$transaction) {
            Log::warning('Webhook PawaPay : transaction introuvable', ['deposit_id' => $depositId]);
            return false;
        }

        // Déjà traité
        if ($transaction->statut !== Transaction::STATUT_EN_ATTENTE) {
            Log::info('Webhook PawaPay : transaction déjà traitée', [
                'deposit_id' => $depositId,
                'statut' => $transaction->statut,
            ]);
            return true;
        }

        return $this->traiterStatut($transaction, $statutPawapay, $payload);
    }

    /**
     * Vérifier la signature du webhook PawaPay (RFC-9421)
     * Vérifie le Content-Digest (SHA-256/SHA-512 du body)
     */
    public function verifierSignature(Request $request): bool
    {
        $contentDigest = $request->header('Content-Digest');

        if (!$contentDigest) {
            Log::warning('Webhook PawaPay : Content-Digest manquant');
            return false;
        }

        $body = $request->getContent();

        // Content-Digest peut être sha-256=:base64: ou sha-512=:base64:
        if (preg_match('/sha-256=:(.+):/', $contentDigest, $matches)) {
            $expected = base64_encode(hash('sha256', $body, true));
            return hash_equals($expected, $matches[1]);
        }

        if (preg_match('/sha-512=:(.+):/', $contentDigest, $matches)) {
            $expected = base64_encode(hash('sha512', $body, true));
            return hash_equals($expected, $matches[1]);
        }

        Log::warning('Webhook PawaPay : format Content-Digest non reconnu', [
            'content_digest' => $contentDigest,
        ]);

        return false;
    }

    /**
     * Vérifier toutes les transactions en attente (tâche planifiée)
     */
    public function reconcilierTransactionsEnAttente(): int
    {
        $transactions = Transaction::where('statut', Transaction::STATUT_EN_ATTENTE)
            ->where(function ($q) {
                $q->where('gateway', Transaction::GATEWAY_PAWAPAY)
                  ->orWhereNull('gateway');
            })
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
                Log::error('Réconciliation PawaPay : erreur', [
                    'deposit_id' => $transaction->deposit_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($traitees > 0) {
            Log::info("Réconciliation PawaPay : {$traitees} transactions vérifiées");
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
     * Envoyer un dépôt à PawaPay (POST /v2/deposits)
     */
    private function envoyerDepot(string $depositId, string $telephone, string $provider, int $montant, string $clientRef): array
    {
        $payload = [
            'depositId' => $depositId,
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $this->formaterTelephone($telephone),
                    'provider' => $provider,
                ],
            ],
            'amount' => (string) $montant,
            'currency' => config('concours.currency', 'XOF'),
            'clientReferenceId' => $clientRef,
        ];

        $response = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/v2/deposits", $payload);

        if (!$response->successful()) {
            Log::error('PawaPay dépôt échoué', [
                'deposit_id' => $depositId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException("Erreur PawaPay ({$response->status()}) : impossible d'initier le dépôt");
        }

        return $response->json();
    }

    /**
     * Formater le numéro de téléphone au format international (229XXXXXXXX)
     */
    private function formaterTelephone(string $telephone): string
    {
        // Nettoyer le numéro
        $tel = preg_replace('/[^0-9]/', '', $telephone);

        // Ajouter le préfixe Bénin si absent
        if (!str_starts_with($tel, '229')) {
            $tel = '229' . $tel;
        }

        return $tel;
    }

    /**
     * Logique commune : mettre à jour vote/don après confirmation de paiement
     */
    private function traiterStatut(Transaction $transaction, string $statutPawapay, array $responseData): bool
    {
        $nouveauStatut = Transaction::mapStatutPawapay($statutPawapay);

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

            Log::info('Paiement PawaPay traité', [
                'deposit_id' => $transaction->deposit_id,
                'type' => $transaction->type,
                'statut' => $nouveauStatut,
            ]);

            return $nouveauStatut === Transaction::STATUT_REUSSI;
        });
    }
}
