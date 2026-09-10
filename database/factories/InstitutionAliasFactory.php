<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\InstitutionAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstitutionAlias>
 */
class InstitutionAliasFactory extends Factory
{
    protected $model = InstitutionAlias::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'alias' => fake()->words(3, true),
            'locale' => 'en',
            'is_primary' => false,
        ];
    }
}
