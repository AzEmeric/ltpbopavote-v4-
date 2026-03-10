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
    private function moneroo(): MonerooService
    {
        return app(MonerooService::class);
    }

    /**
     * Page d'accueil — gère aussi le retour Moneroo avec paymentId
     */
    public function accueil(Request $request)
    {
        $paymentId = $request->query('paymentId');

        if ($paymentId) {
            try {
                $this->moneroo()->traiterRetour($paymentId);
            } catch (\Exception $e) {
                Log::error('Erreur traitement retour Moneroo (accueil)', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('welcome');
    }

    /**
     * Initier un paiement pour un vote (redirection Moneroo)
     */
    public function initierVote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vote_id' => 'required|integer|exists:votes,id',
            'telephone' => 'nullable|string|min:8|max:20',
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

            $result = $this->moneroo()->initierPaiementVote($vote, $request->telephone);

            return response()->json([
                'success' => true,
                'message' => 'Redirection vers la page de paiement.',
                'checkout_url' => $result['checkout_url'],
                'payment_id' => $result['payment_id'],
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
     * Initier un paiement pour un don (redirection Moneroo)
     */
    public function initierDon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:' . config('concours.min_don', 100),
            'telephone' => 'nullable|string|min:8|max:20',
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

            $result = $this->moneroo()->initierPaiementDon($don, $request->telephone ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Redirection vers la page de paiement.',
                'checkout_url' => $result['checkout_url'],
                'payment_id' => $result['payment_id'],
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
     * Vérifier le statut d'un paiement Moneroo
     */
    public function verifierDepot(Request $request): JsonResponse
    {
        $paymentId = $request->query('deposit_id') ?? $request->query('payment_id');

        if (!$paymentId) {
            return response()->json([
                'success' => false,
                'message' => 'payment_id requis',
            ], 400);
        }

        try {
            $transaction = Transaction::where('deposit_id', $paymentId)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction introuvable',
                ], 404);
            }

            // Si encore en attente, tenter une vérification via l'API
            if ($transaction->statut === Transaction::STATUT_EN_ATTENTE) {
                $this->moneroo()->verifierPaiement($paymentId);
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
            Log::error('Erreur vérification paiement Moneroo', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le paiement',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Retour après paiement Moneroo — vérifie le statut et redirige vers l'accueil
     */
    public function retourPaiement(Request $request)
    {
        $paymentId = $request->query('paymentId');

        if ($paymentId) {
            try {
                $this->moneroo()->traiterRetour($paymentId);
            } catch (\Exception $e) {
                Log::error('Erreur traitement retour Moneroo', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Rediriger vers l'accueil en conservant les params pour le JS (modal succès)
        $query = [];
        if ($paymentId) {
            $query['paymentId'] = $paymentId;
        }
        if ($request->query('paymentStatus')) {
            $query['paymentStatus'] = $request->query('paymentStatus');
        }

        return redirect('/?' . http_build_query($query));
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
            $resultats = $this->moneroo()->rechercherParTelephone($request->telephone);

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
                    'deposit_id' => 'SIM-' . uniqid(),
                    'montant' => $vote->montant_total,
                    'statut' => $statut === 'reussi' ? Transaction::STATUT_REUSSI : Transaction::STATUT_ECHOUE,
                    'type' => Transaction::TYPE_VOTE,
                    'telephone' => $vote->telephone,
                    'gateway' => Transaction::GATEWAY_MONEROO,
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
                    'gateway' => Transaction::GATEWAY_MONEROO,
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
