<?php

namespace App\Console\Commands;

use App\Services\PawapayService;
use App\Services\FeexpayService;
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
    protected $description = 'Vérifie les transactions en attente via PawaPay et FeexPay et met à jour les statuts';

    /**
     * Exécuter la commande
     */
    public function handle(PawapayService $pawapayService, FeexpayService $feexpayService): int
    {
        $this->info('Réconciliation des paiements en attente...');

        $traiteesPawapay = $pawapayService->reconcilierTransactionsEnAttente();
        $this->info("PawaPay : {$traiteesPawapay} transaction(s) vérifiée(s).");

        $traiteesFeexpay = $feexpayService->reconcilierTransactionsEnAttente();
        $this->info("FeexPay : {$traiteesFeexpay} transaction(s) vérifiée(s).");

        $total = $traiteesPawapay + $traiteesFeexpay;
        $this->info("Total : {$total} transaction(s) vérifiée(s).");

        return Command::SUCCESS;
    }
}
