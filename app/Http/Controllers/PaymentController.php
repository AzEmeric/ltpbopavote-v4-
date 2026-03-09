<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Don;
use App\Models\Transaction;
use App\Services\MonerooService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(
        private MonerooService $monerooService
    ) {}

    /**
     * Initier un paiement pour un vote
     */
    public function initierVote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vote_id' => 'required|integer|exists:votes,id',
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

            // Vérifier que le vote est bien en attente
            if (!$vote->estEnAttente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce vote a déjà été traité',
                ], 400);
            }

            $result = $this->monerooService->initierPaiementVote($vote);

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'checkout_url' => $result['checkout_url'],
                'moneroo_id' => $result['moneroo_id'],
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
     * Initier un paiement pour un don
     */
    public function initierDon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:' . config('concours.min_don', 100),
            'telephone' => 'required|string|min:8|max:20',
            'nom_donateur' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:500',
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

            $result = $this->monerooService->initierPaiementDon($don);

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'checkout_url' => $result['checkout_url'],
                'moneroo_id' => $result['moneroo_id'],
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
     * Webhook Moneroo — réception des notifications de paiement
     */
    public function webhook(Request $request): JsonResponse
    {
        // Vérifier la signature
        $signature = $request->header('X-Moneroo-Signature', '');
        $rawPayload = $request->getContent();

        if (!$this->monerooService->verifierSignature($rawPayload, $signature)) {
            Log::warning('Webhook Moneroo : signature invalide', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['success' => false, 'message' => 'Signature invalide'], 403);
        }

        try {
            Log::info('Webhook Moneroo reçu', [
                'event' => $request->input('event'),
                'moneroo_id' => $request->input('data.id'),
            ]);

            $this->monerooService->traiterWebhook($request->all());

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erreur webhook Moneroo', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * COUCHE 1 : Vérification au retour de l'utilisateur
     * Appelé quand l'utilisateur revient de Moneroo (return_url)
     */
    public function retour(Request $request): JsonResponse
    {
        $paymentId = $request->query('paymentId');
        $paymentStatus = $request->query('paymentStatus');

        if (!$paymentId) {
            return response()->json([
                'success' => false,
                'message' => 'ID de paiement manquant',
            ], 400);
        }

        try {
            // Vérifier directement via l'API Moneroo (ne pas se fier au query param)
            $reussi = $this->monerooService->verifierPaiement($paymentId);

            $transaction = Transaction::where('moneroo_id', $paymentId)->first();

            return response()->json([
                'success' => true,
                'paiement_reussi' => $reussi,
                'statut' => $transaction?->statut ?? 'inconnu',
                'type' => $transaction?->type ?? 'inconnu',
                'montant' => $transaction?->montant,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vérification retour Moneroo', [
                'error' => $e->getMessage(),
                'paymentId' => $paymentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * COUCHE 3 : Recherche par téléphone
     * Permet aux votants de vérifier le statut de leurs votes/dons
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
            $resultats = $this->monerooService->rechercherParTelephone($request->telephone);

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

            if ($voteId) {
                $vote = Vote::findOrFail($voteId);
                if (!$vote->estEnAttente()) {
                    return response()->json(['success' => false, 'message' => 'Vote déjà traité'], 400);
                }
                $vote->statut_paiement = $statut === 'reussi' ? Vote::STATUT_REUSSI : Vote::STATUT_ECHOUE;
                $vote->save();

                Transaction::create([
                    'vote_id' => $vote->id,
                    'moneroo_id' => 'SIM-' . uniqid(),
                    'montant' => $vote->montant_total,
                    'statut' => $statut === 'reussi' ? Transaction::STATUT_REUSSI : Transaction::STATUT_ECHOUE,
                    'type' => Transaction::TYPE_VOTE,
                    'telephone' => $vote->telephone,
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
                    'moneroo_id' => 'SIM-' . uniqid(),
                    'montant' => $don->montant,
                    'statut' => $statut === 'reussi' ? Transaction::STATUT_REUSSI : Transaction::STATUT_ECHOUE,
                    'type' => Transaction::TYPE_DON,
                    'telephone' => $don->telephone,
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
