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
            // Cambia el tipo de la columna 'created_at' a timestamp
            $table->timestamp('created_at')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            // Revierte el tipo de la columna 'created_at' a su tipo original
            // Esto puede variar dependiendo del tipo original de la columna.
            // Por ejemplo, si el tipo original era DATETIME:
            $table->dateTime('created_at')->change();
        });
    }
};
