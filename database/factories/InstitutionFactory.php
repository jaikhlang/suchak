<?php

namespace Database\Factories;

use App\Enums\InstitutionType;
use App\Models\Institution;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Commission';
        $slug = Str::slug($name);
        $domain = $slug.'.gov.in';

        return [
            'name' => $name,
            'short_name' => strtoupper(fake()->lexify('???')),
            'slug' => $slug,
            'institution_type' => fake()->randomElement(InstitutionType::cases()),
            'parent_id' => null,
            'state_id' => null,
            'website_url' => "https://{$domain}",
            'official_domain' => $domain,
            'is_verified' => true,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}
