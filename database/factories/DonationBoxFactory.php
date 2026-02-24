<?php

namespace Database\Factories;

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationBox>
 */
class DonationBoxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'purpose' => fake()->paragraphs(2, true),
            'target_amount' => fake()->randomElement([null, fake()->randomFloat(2, 100, 10000)]),
            'current_amount' => 0,
            'currency' => 'RON',
            'visibility' => fake()->randomElement(DonationBoxVisibility::cases()),
            'status' => DonationBoxStatus::Open,
        ];
    }

    /**
     * Indicate that the donation box has no target amount (open-ended).
     */
    public function openEnded(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_amount' => null,
        ]);
    }

    /**
     * Indicate that the donation box is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DonationBoxStatus::Closed,
        ]);
    }

    /**
     * Indicate that the donation box is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => DonationBoxVisibility::Private,
        ]);
    }

    /**
     * Indicate that the donation box is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => DonationBoxVisibility::Public,
        ]);
    }

    /**
     * Set a specific target amount.
     */
    public function withTargetAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'target_amount' => $amount,
        ]);
    }
}
