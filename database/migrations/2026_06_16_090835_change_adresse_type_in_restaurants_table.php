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
        Schema::table('restaurants', function (Blueprint $table) {
            // Note de soutenance : On a modifié le type de 'adresse' de string (255) en 'text'
            // car la description d'un itinéraire précis (repères visuels, carrefours, etc.) à Cotonou
            // dépasse régulièrement la limite standard de 255 caractères d'une adresse classique.
            $table->text('adresse')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('adresse')->change();
        });
    }
};
