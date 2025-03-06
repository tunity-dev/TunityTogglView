<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeLog>
 */
class TimeLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->user_id ?? 1,
            'project_id' => Project::inRandomOrder()->first()->project_id ?? 1,
            'api_token' => Str::random(20),
            'start_time' => $this->faker->dateTimeThisMonth(),
            'end_time' => $this->faker->dateTimeThisMonth(),
            'total_time' => $this->faker->numberBetween(30, 480),
            'duration' => $this->faker->numberBetween(30, 480),
            'duronly' => $this->faker->numberBetween(30, 480),
        ];
    }
}
