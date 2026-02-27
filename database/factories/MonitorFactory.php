<?php

namespace Database\Factories;

use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorMethodEnum;
use App\Enums\MonitorStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Monitor>
 */
class MonitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'name'            => fake()->words(3, true),
            'url'             => fake()->url(),
            'method'          => fake()->randomElement(MonitorMethodEnum::cases())->value,
            'interval'        => fake()->randomElement(MonitorIntervalEnum::cases())->value,
            'timeout'         => fake()->numberBetween(5, 30),
            'fail_threshold'  => fake()->numberBetween(1, 5),
            'notify_email'    => fake()->safeEmail(),
            'is_active'       => true,
            'status'          => MonitorStatusEnum::UNKNOWN->value,
            'last_checked_at' => null,
        ];
    }

    /**
     * Indicate the monitor is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate the monitor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate the monitor is down.
     */
    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MonitorStatusEnum::DOWN->value,
        ]);
    }
}
