<?php

namespace Database\Factories;

use App\Enums\MonitorStatusEnum;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonitorLog>
 */
class MonitorLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'monitor_id'      => Monitor::factory(),
            'status'          => fake()->randomElement(MonitorStatusEnum::cases())->value,
            'response_code'   => fake()->randomElement([200, 201, 301, 400, 404, 500, null]),
            'response_time_ms'=> fake()->numberBetween(50, 5000),
            'checked_at'      => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate the check result was up.
     */
    public function up(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => MonitorStatusEnum::UP->value,
            'response_code' => 200,
        ]);
    }

    /**
     * Indicate the check result was down.
     */
    public function down(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => MonitorStatusEnum::DOWN->value,
            'response_code' => null,
        ]);
    }
}
