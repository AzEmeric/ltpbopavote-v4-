<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Don;
use App\Models\Transaction;
use App\Services\PawapayService;
use App\Services\FeexpayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private function pawapay(): PawapayService
    {
        return app(PawapayService::class);
    }

    private function feexpay(): FeexpayService
    {
        return app(FeexpayService::class);
    }

    /**
     * Retourner le service de paiement correspondant à la gateway
     */
    private function getService(string $gateway): PawapayService|FeexpayService
    {
        return match ($gateway) {
            Transaction::GATEWAY_FEEXPAY => $this->feexpay(),
            default => $this->pawapay(),
        };
    }

    /**
     * Initier un paiement pour un vote (STK Push)
     */
    public function initierVote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vote_id' => 'required|integer|exists:votes,id',
            'telephone' => 'required|string|min:8|max:20',
            'operateur' => 'required|string|in:MTN,MOOV',
            'gateway' => 'sometimes|string|in:pawapay,feexpay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $vote = Vote::findOrFail($request->vote_id);

            if (!$vote->estEnAttente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce vote a déjà été traité',
                ], 400);
            }

            // Sauvegarder le téléphone sur le vote
            if (!$vote->telephone) {
                $vote->telephone = $request->telephone;
                $vote->save();
            }

            $gateway = $request->input('gateway', Transaction::GATEWAY_PAWAPAY);
            $service = $this->getService($gateway);
            $result = $service->initierPaiementVote($vote, $request->telephone, $request->operateur);

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié. Confirmez sur votre téléphone.',
                'deposit_id' => $result['deposit_id'],
                'vote_id' => $vote->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur initiation paiement vote', [
                'error' => $e->getMessage(),
                'vote_id' => $request->vote_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Initier un paiement pour un don (STK Push)
     */
    public function initierDon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:' . config('concours.min_don', 100),
            'telephone' => 'required|string|min:8|max:20',
            'operateur' => 'required|string|in:MTN,MOOV',
            'nom_donateur' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:500',
            'gateway' => 'sometimes|string|in:pawapay,feexpay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $don = DB::transaction(function () use ($request) {
                return Don::create([
                    'telephone' => $request->telephone,
                    'montant' => $request->montant,
                    'nom_donateur' => $request->nom_donateur,
                    'message' => $request->message,
                    'ip_address' => $request->ip(),
                ]);
            });

            $gateway = $request->input('gateway', Transaction::GATEWAY_PAWAPAY);
            $service = $this->getService($gateway);
            $result = $service->initierPaiementDon($don, $request->telephone, $request->operateur);

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié. Confirmez sur votre téléphone.',
                'deposit_id' => $result['deposit_id'],
                'don_id' => $don->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur initiation paiement don', [
                'error' => $e->getMessage(),
                'montant' => $request->montant,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Webhook PawaPay — réception des notifications de paiement
     */
    public function webhook(Request $request): JsonResponse
    {
        // Vérifier la signature (Content-Digest RFC-9421)
        if (!$this->pawapay()->verifierSignature($request)) {
            Log::warning('Webhook PawaPay : signature invalide', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['success' => false, 'message' => 'Signature invalide'], 403);
        }

        try {
            Log::info('Webhook PawaPay reçu', [
                'deposit_id' => $request->input('depositId'),
                'status' => $request->input('status'),
            ]);

            $this->pawapay()->traiterWebhook($request->all());

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erreur webhook PawaPay', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Webhook FeexPay — réception des notifications de paiement
     */
    public function webhookFeexpay(Request $request): JsonResponse
    {
        try {
            Log::info('Webhook FeexPay reçu', [
                'reference' => $request->input('reference') ?? $request->input('transref'),
                'status' => $request->input('status'),
            ]);

            $this->feexpay()->traiterWebhook($request->all());

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erreur webhook FeexPay', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Vérifier le statut d'un dépôt PawaPay
     */
    public function verifierDepot(Request $request): JsonResponse
    {
        $depositId = $request->query('deposit_id');

        if (!$depositId) {
            return response()->json([
                'success' => false,
                'message' => 'deposit_id requis',
            ], 400);
        }

        try {
            $transaction = Transaction::where('deposit_id', $depositId)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction introuvable',
                ], 404);
            }

            // Si encore en attente, tenter une vérification via l'API
            if ($transaction->statut === Transaction::STATUT_EN_ATTENTE) {
                $service = $this->getService($transaction->gateway ?? Transaction::GATEWAY_PAWAPAY);
                $service->verifierPaiement($depositId);
                $transaction->refresh();
            }

            return response()->json([
                'success' => true,
                'statut' => $transaction->statut,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'paiement_reussi' => $transaction->statut === Transaction::STATUT_REUSSI,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vérification dépôt PawaPay', [
                'error' => $e->getMessage(),
                'deposit_id' => $depositId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Recherche par téléphone
     */
    public function rechercher(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string|min:8|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone invalide',
            ], 422);
        }

        try {
            $resultats = $this->pawapay()->rechercherParTelephone($request->telephone);

            return response()->json([
                'success' => true,
                'resultats' => $resultats,
                'total_votes' => $resultats['votes']->count(),
                'total_dons' => $resultats['dons']->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur recherche par téléphone', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
            ], 500);
        }
    }

    /**
     * Simulation de paiement (développement uniquement)
     */
    public function simulate(Request $request): JsonResponse
    {
        if (!config('concours.payment_simulation', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation désactivée',
            ], 403);
        }

        try {
            $voteId = $request->query('vote_id');
            $donId = $request->query('don_id');
            $statut = $request->query('statut', 'reussi');

            if (!$voteId && !$donId) {
                return response()->json([
                    'success' => false,
                    'message' => 'vote_id ou don_id requis',
                ], 400);
            }

            DB::beginTransaction();

            $gateway = $request->query('gateway', Transaction::GATEWAY_PAWAPAY);

            if ($voteId) {
                $vote = Vote::findOrFail($voteId);
                if (!$vote->estEnAttente()) {
                    return response()->json(['success' => false, 'message' => 'Vote déjà traité'], 400);
                }
                $vote->statut_paiement = $statut === 'reussi' ? Vote::STATUT_REUSSI : Vote::STATUT_ECHOUE;
                $vote->save();

                Transaction::create([
                    'vote_id' => $vote->id,
                    'deposit_id' => 'SIM-' . uniqid(),
                    'montant' => $vote->montant_total,
                    'statut' => $statut === 'reussi' ? Transaction::STATUT_REUSSI : Transaction::STATUT_ECHOUE,
                    'type' => Transaction::TYPE_VOTE,
                    'telephone' => $vote->telephone,
                    'operateur' => 'MTN',
                    'gateway' => $gateway,
                    'response_data' => ['simulation' => true, 'date' => now()->toDateTimeString()],
                    'processed_at' => now(),
                ]);
            }

            if ($donId) {
                $don = Don::findOrFail($donId);
                $don->statut = $statut === 'reussi' ? Don::STATUT_REUSSI : Don::STATUT_ECHOUE;
                $don->save();

                Transaction::create([
                    'don_id' => $don->id,
                    'deposit_id' => 'SIM-' . uniqid(),
                    'montant' => $don->montant,
                    'statut' => $statut === 'reussi' ? Transaction::STATUT_REUSSI : Transaction::STATUT_ECHOUE,
                    'type' => Transaction::TYPE_DON,
                    'telephone' => $don->telephone,
                    'operateur' => 'MTN',
                    'gateway' => $gateway,
                    'response_data' => ['simulation' => true, 'date' => now()->toDateTimeString()],
                    'processed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Simulation réussie',
                'statut' => $statut,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur simulation',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
