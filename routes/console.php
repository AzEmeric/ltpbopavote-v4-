<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Tâches planifiées
|--------------------------------------------------------------------------
*/

// Réconciliation des paiements Moneroo toutes les 5 minutes
Schedule::command('paiements:reconcilier')->everyFiveMinutes();
