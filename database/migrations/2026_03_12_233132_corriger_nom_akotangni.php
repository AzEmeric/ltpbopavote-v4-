<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('candidats')
            ->where('nom', 'AKOTEGNIN')
            ->update(['nom' => 'AKOTANGNI']);
    }

    public function down(): void
    {
        DB::table('candidats')
            ->where('nom', 'AKOTANGNI')
            ->update(['nom' => 'AKOTEGNIN']);
    }
};
