<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'user_id' => \App\Models\User::factory(),
            'series_id' => \App\Models\Series::factory(),
            'function_series_id' => \App\Models\FunctionSerie::factory(),
            'is_divided' => $this->faker->boolean,
            'num_chapter' => $this->faker->numberBetween(1, 100),
            'created_at' => $this->faker->date(),
        ];
    }
}
