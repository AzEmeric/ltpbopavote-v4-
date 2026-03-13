<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CandidatController extends Controller
{
    /**
     * Récupérer tous les candidats
     */
    public function index(): JsonResponse
    {
        try {
            $candidats = Candidat::where('actif', true)->populaires()->get();
            $filieres = array_keys(config('concours.filieres'));

            $parFiliere = [];
            foreach ($filieres as $code) {
                $parFiliere[$code] = $candidats->where('filiere', $code)->values();
            }

            return response()->json([
                'success' => true,
                'count' => $candidats->count(),
                'candidats' => $candidats,
                'par_filiere' => $parFiliere,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des candidats',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Récupérer les candidats d'une filière
     */
    public function parFiliere(string $filiere): JsonResponse
    {
        try {
            $filieresValides = array_keys(config('concours.filieres'));

            if (!in_array($filiere, $filieresValides)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Filière invalide',
                ], 400);
            }

            $candidats = Candidat::where('actif', true)->filiere($filiere)->populaires()->get();

            return response()->json([
                'success' => true,
                'filiere' => $filiere,
                'count' => $candidats->count(),
                'candidats' => $candidats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des candidats',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Récupérer un candidat par ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $candidat = Candidat::findOrFail($id);

            return response()->json([
                'success' => true,
                'candidat' => $candidat,
                'statistiques' => $candidat->getStatistiques(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Candidat introuvable',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du candidat',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques globales
     */
    public function statistiques(): JsonResponse
    {
        try {
            $totalCandidats = Candidat::where('actif', true)->count();
            $totalVotes = Candidat::where('actif', true)->sum('total_votes');
            $filieres = array_keys(config('concours.filieres'));

            $parFiliere = [];
            foreach ($filieres as $code) {
                $parFiliere[$code] = [
                    'candidats' => Candidat::filiere($code)->count(),
                    'votes' => Candidat::getTotalVotesParFiliere($code),
                ];
            }

            $topCandidats = Candidat::where('actif', true)->populaires()->take(5)->get();

            return response()->json([
                'success' => true,
                'total_candidats' => $totalCandidats,
                'total_votes' => $totalVotes,
                'par_filiere' => $parFiliere,
                'top_candidats' => $topCandidats,
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
