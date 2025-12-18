<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Insurance>
 */
class InsuranceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->words(3, true));
        $abbr = '';

        $arrName = explode(' ', $name);
        foreach ($arrName as $word) {
            $abbr .= $word[0];
        }

        return [
            'abbreviation' => $abbr,
            'common_name' => $name,
            'software_id' => 1,
            'status' => 'approved',
        ];
    }
}
