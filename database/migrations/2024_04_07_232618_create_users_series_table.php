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
        Schema::create('users_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id'); // Change this
            $table->unsignedBigInteger('user_id'); // And this
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_series');
    }
};
