<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => $this->faker->randomElement(['full_name', 'middle_initial', 'avatar', 'gender', 'bio', 'website']),
            'value' => $this->faker->sentence(),
            'icon' => $this->faker->boolean(50) ? $this->faker->word().'.svg' : null,
            'status' => $this->faker->randomElement(['0', '1']),
            'type' => $this->faker->randomElement(['detail', 'preference', 'setting']),
            'user_id' => \App\Models\User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status' => '1',
        ]);
    }

    public function inactive(): static
    {
        return $this->state([
            'status' => '0',
        ]);
    }

    public function forUser($user): static
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }
}