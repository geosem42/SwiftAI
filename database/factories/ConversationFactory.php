<?php

namespace Database\Factories;

use App\Models\Personality;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'title' => $this->faker->sentence,
            'personality_id' => Personality::factory()->create()->id
        ];
    }
}
