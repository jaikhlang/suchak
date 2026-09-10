<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' State',
            'iso_code' => 'IN-'.strtoupper(fake()->unique()->lexify('??')),
            'type' => 'state',
            'capital' => fake()->city(),
            'is_active' => true,
        ];
    }
}
