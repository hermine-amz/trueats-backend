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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('bloque_jusqua')->nullable()->after('compte_active');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('est_valide')->default(false)->after('superficie');
            $table->timestamp('bloque_jusqua')->nullable()->after('est_valide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bloque_jusqua');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['est_valide', 'bloque_jusqua']);
        });
    }
};
