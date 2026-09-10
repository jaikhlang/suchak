<?php

namespace Database\Factories;

use App\Models\CandidateSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CandidateSubscription>
 */
class CandidateSubscriptionFactory extends Factory
{
    protected $model = CandidateSubscription::class;

    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'state_id' => null,
            'institution_id' => null,
            'reservation_category' => null,
            'is_verified' => true,
            'verification_token' => Str::random(32),
            'last_notified_at' => null,
        ];
    }
}
