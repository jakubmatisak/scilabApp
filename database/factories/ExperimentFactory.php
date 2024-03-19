<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experiment>
 */
class ExperimentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_name' => $this->faker->word . '.txt',
            'name' => $this->faker->sentence,
            'context' => "{}",
            'output' => "[]",
            'created_by' => \App\Models\User::factory(),
            'file_path' => $this->faker->filePath,
        ];
    }
}
