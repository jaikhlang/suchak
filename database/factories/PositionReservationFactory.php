<?php

namespace Database\Factories;

use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Models\Position;
use App\Models\PositionReservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PositionReservation>
 */
class PositionReservationFactory extends Factory
{
    protected $model = PositionReservation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'category' => ReservationCategory::UR,
            'quota_type' => QuotaType::Vertical,
            'vacancies' => 20,
        ];
    }
}
