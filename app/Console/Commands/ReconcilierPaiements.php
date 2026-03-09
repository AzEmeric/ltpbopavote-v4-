<?php

namespace App\Console\Commands;

use App\Services\MonerooService;
use Illuminate\Console\Command;

class ReconcilierPaiements extends Command
{
    /**
     * Nom et signature de la commande
     */
    protected $signature = 'paiements:reconcilier';

    /**
     * Description de la commande
     */
    protected $description = 'Vérifie les transactions en attente via l\'API Moneroo et met à jour les statuts';

    /**
     * Exécuter la commande
     */
    public function handle(MonerooService $monerooService): int
    {
        $this->info('Réconciliation des paiements en attente...');

        $traitees = $monerooService->reconcilierTransactionsEnAttente();

        $this->info("{$traitees} transaction(s) vérifiée(s).");

        return Command::SUCCESS;
    }
}
