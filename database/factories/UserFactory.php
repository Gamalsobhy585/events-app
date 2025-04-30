<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $prefix = $this->faker->randomElement(['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', null]);
        
        return [
            'firstname' => $this->faker->firstName(),
            'middlename' => $this->faker->boolean(70) ? $this->faker->firstName() : null,
            'lastname' => $this->faker->lastName(),
            'prefixname' => $prefix,
            'photo' => $this->faker->boolean(80) ? 'avatars/'.$this->faker->image(storage_path('app/public/avatars'), 100, 100, 'people', false) : null,
            'email' => $this->faker->unique()->safeEmail(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function male(): static
    {
        return $this->state([
            'prefixname' => $this->faker->randomElement(['Mr', 'Sir', 'Dr']),
        ]);
    }

    public function female(): static
    {
        return $this->state([
            'prefixname' => $this->faker->randomElement(['Mrs', 'Ms', 'Miss', 'Madam']),
        ]);
    }

    public function withPhoto(): static
    {
        return $this->state([
            'photo' => 'avatars/'.$this->faker->image(storage_path('app/public/avatars'), 100, 100, 'people', false),
        ]);
    }
}