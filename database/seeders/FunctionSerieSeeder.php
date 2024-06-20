<?php

namespace Database\Seeders;

use App\Models\FunctionSerie;
use App\Models\Series;
use App\Models\TypeFunction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FunctionSerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $series = Series::all();
        $types = TypeFunction::all();

        foreach ($series as $serie) {
            foreach ($types as $type) {
                FunctionSerie::factory()->create([
                    'series_id' => $serie->id,
                    'type_id' => $type->id,
                ]);
            }
        }
    }
}
