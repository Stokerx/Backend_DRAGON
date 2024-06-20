<?php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeriesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Series::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
          'img_url' => $this->faker->imageUrl(),
            'name' => $this->faker->sentence(3),
            'day_issue' => $this->faker->dayOfWeek(),
            'status' => $this->faker->randomElement(['Emision', 'Finalizado', 'pausado']),
            'classification' => $this->faker->randomElement(['free', 'premium']),
            'created_at' => $this->faker->date(),

        ];
    }
}