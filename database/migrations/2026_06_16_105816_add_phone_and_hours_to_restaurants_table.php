<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note de soutenance : telephone et horaires ont été ajoutés pour enrichir l'expérience utilisateur.
        // L'utilisation de types string simples (nullable) permet de s'adapter aux différents formats d'horaires locaux (ex: "8h-22h", "Midi/Soir") et de numéros de téléphone locaux ou internationaux.
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('telephone')->nullable()->after('nom');
            $table->string('horaires')->nullable()->after('telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'horaires']);
        });
    }
};
