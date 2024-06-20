<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFunctionSeriesToUsersHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_history', function (Blueprint $table) {
            $table->foreignId('function_series_id')->after('chapter_id')->constrained()->onDelete('cascade');
            $table->decimal('function_value', 8, 2)->after('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_history', function (Blueprint $table) {
            $table->dropColumn('function_series_id');
            $table->dropColumn('function_value');
        });
    }
}
