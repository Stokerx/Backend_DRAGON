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
        Schema::create('activity_series_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id')->nullable(); // Allow NULL values
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_system_activity');
            $table->date('created_at');
            $table->longText('description');
            $table->foreign('series_id')->references('id')->on('series')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_series_users');
    }
};
