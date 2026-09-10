<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Models\Notice;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notice_id' => Notice::factory(),
            'title' => fake()->jobTitle(),
            'post_code' => 'P'.fake()->randomNumber(3),
            'department' => 'Civil Engineering',
            'total_vacancies' => 50,
            'employment_type' => EmploymentType::Permanent,
            'pay_level' => 'Level 10 (7th CPC)',
            'pay_scale_text' => 'Rs. 56,100 - 1,77,500/-',
            'salary_min' => 56100,
            'salary_max' => 177500,
            'metadata' => [],
        ];
    }
}
