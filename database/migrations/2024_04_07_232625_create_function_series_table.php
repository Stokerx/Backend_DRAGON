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
        Schema::create('function_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('type_id');
            $table->decimal('value', 10, 2);
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('type_function')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('function_series');
    }
};
