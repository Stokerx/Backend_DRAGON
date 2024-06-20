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
        DB::table('type_function')->insert([
            ['name' => 'Traducción KR'],
            ['name' => 'Traducción EN'],
            ['name' => 'Limpieza'],
            ['name' => 'Edición'],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('type_function')->whereIn('name', ['KR', 'EN', 'REDRAW', 'TYPER'])->delete();
    }
};