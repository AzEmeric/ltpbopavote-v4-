<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    /**
     * Créer un nouveau vote
     */
    public function store(Request $request): JsonResponse
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'candidat_id' => 'required|integer|exists:candidats,id',
            'nombre_votes' => 'required|integer|min:1|max:900',
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
            // Reset connexion pour éviter les transactions zombies (Neon pooler)
            DB::reconnect();

            // Vérifier que le candidat existe
            $candidat = Candidat::findOrFail($request->candidat_id);

            // Calculer le montant automatiquement
            $prixUnitaire = config('concours.vote_price', 100);
            $montantTotal = $request->nombre_votes * $prixUnitaire;

            // Créer le vote
            $vote = Vote::create([
                'candidat_id' => $request->candidat_id,
                'nombre_votes' => $request->nombre_votes,
                'montant_total' => $montantTotal,
                'telephone' => $request->telephone,
                'statut_paiement' => Vote::STATUT_EN_ATTENTE,
                'ip_address' => $request->ip(),
            ]);

            // Log de l'opération
            Log::info('Vote créé', [
                'vote_id' => $vote->id,
                'candidat' => $candidat->nom_complet,
                'nombre_votes' => $vote->nombre_votes,
                'montant' => $vote->montant_total,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vote créé avec succès',
                'vote_id' => $vote->id,
                'transaction_id' => $vote->transaction_id,
                'candidat_id' => $vote->candidat_id,
                'nombre_votes' => $vote->nombre_votes,
                'montant_total' => $vote->montant_total,
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Candidat introuvable',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Erreur création vote', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du vote',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Récupérer un vote par ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $vote = Vote::with(['candidat', 'transaction'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'vote' => $vote,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vote introuvable',
            ], 404);
        }
    }

    /**
     * Récupérer les votes d'un candidat
     */
    public function parCandidat(int $candidatId): JsonResponse
    {
        try {
            $candidat = Candidat::findOrFail($candidatId);
            $votes = Vote::where('candidat_id', $candidatId)
                ->reussis()
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'candidat' => $candidat,
                'votes' => $votes,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Candidat introuvable',
            ], 404);
        }
    }

    /**
     * Statistiques des votes
     */
    public function statistiques(): JsonResponse
    {
        try {
            $totalVotes = Vote::count();
            $votesReussis = Vote::reussis()->count();
            $votesEnAttente = Vote::enAttente()->count();
            $votesEchoues = Vote::echoues()->count();
            $montantTotal = Vote::reussis()->sum('montant_total');
            
            $tauxReussite = $totalVotes > 0 
                ? round(($votesReussis / $totalVotes) * 100, 2) 
                : 0;

            return response()->json([
                'success' => true,
                'total_votes' => $totalVotes,
                'votes_reussis' => $votesReussis,
                'votes_en_attente' => $votesEnAttente,
                'votes_echoues' => $votesEchoues,
                'montant_total' => $montantTotal,
                'taux_reussite' => $tauxReussite,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
