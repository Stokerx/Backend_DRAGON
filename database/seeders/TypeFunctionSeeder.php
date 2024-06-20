<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeFunction;

class TypeFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $functions = ["Traducción KR", " Traducción EN", "Limpieza", "Edición"];

        foreach ($functions as $function) {
            TypeFunction::factory()->create([
                'name' => $function,
            ]);
        }
    }
}
