<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'contact_no' => fake()->regexify('[0-9]{9}'),
            'mobile_no' => fake()->regexify('[0-9]{9}'),
            'is_approved' => fake()->boolean(90),
            'software_id' => 1,
            'state_id' => State::inRandomOrder()->value('id'),
            'user_id' => 2,
        ];
    }
}
