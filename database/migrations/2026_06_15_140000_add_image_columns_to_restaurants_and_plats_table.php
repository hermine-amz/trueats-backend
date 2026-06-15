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
            $table->string('logo_url', 2048)->nullable();
            $table->string('photo_url', 2048)->nullable();
        });

        Schema::table('plats', function (Blueprint $table) {
            $table->string('image_url', 2048)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'photo_url']);
        });

        Schema::table('plats', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
