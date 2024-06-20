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
        Schema::table('series', function (Blueprint $table) {
            // Añade solo la columna 'updated_at' si no existe
            if (!Schema::hasColumn('series', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            // Elimina la columna 'updated_at' si existe
            if (Schema::hasColumn('series', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};