<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Tâches planifiées
|--------------------------------------------------------------------------
*/

// Réconciliation des paiements PawaPay toutes les 5 minutes
Schedule::command('paiements:reconcilier')->everyFiveMinutes();
