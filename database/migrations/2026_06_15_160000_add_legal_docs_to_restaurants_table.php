<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Infos descriptives manquantes dans la table de base
            $table->string('quartier')->nullable()->after('adresse');
            $table->string('categorie')->nullable()->after('quartier');
            $table->string('type_cuisine')->nullable()->after('categorie');

            // Documents légaux du gérant/établissement
            // On stocke les URLs des fichiers uploadés sur storage public
            $table->string('cip_url')->nullable()->after('photo_url');
            $table->string('ifu_numero', 50)->nullable()->after('cip_url');
            $table->string('ifu_attestation_url')->nullable()->after('ifu_numero');
            $table->string('rccm_numero', 100)->nullable()->after('ifu_attestation_url');
            $table->string('rccm_extrait_url')->nullable()->after('rccm_numero');

            // Raison de rejet par l'admin (optionnel)
            $table->text('motif_rejet')->nullable()->after('rccm_extrait_url');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'quartier', 'categorie', 'type_cuisine',
                'cip_url', 'ifu_numero', 'ifu_attestation_url',
                'rccm_numero', 'rccm_extrait_url', 'motif_rejet',
            ]);
        });
    }
};
