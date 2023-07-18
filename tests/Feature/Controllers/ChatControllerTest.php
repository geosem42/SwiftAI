<?php

namespace Tests\Feature\Controllers;

use App\Models\Conversation;
use App\Models\Personality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_can_create_new_conversation_fails_validation()
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->postJson('/new-conversation', [])->assertUnprocessable()
            ->assertJson([
                "title" => [
                    "The title field is required."
                ],
                "personality_id" => [
                    "The personality id field is required."
                ]
            ]);
    }

    /** @test */
    public function test_can_create_new_conversation()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Title',
            'personality_id' => Personality::factory()->create()->id,
        ];

        $this
            ->actingAs($user)
            ->postJson('/new-conversation', $payload)
            ->assertSuccessful()
            ->assertJsonFragment($payload + [
                    'user_id' => $user->id
                ]);
    }

    /** @test */
    public function test_can_get_conversations()
    {
        $user = User::factory()->create();

        $conversations = Conversation::factory()->count(4)->create(
            ['user_id' => $user->id]
        );

        $this
            ->actingAs($user)
            ->getJson('/get-conversations')
            ->assertSuccessful()
            ->assertJsonFragment($conversations->first()->toArray());

    }

    /** @test */
    public function test_can_get_personalities()
    {
        $personalities = Personality::factory()->times(4)->create();

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->getJson('/personalities')->assertSuccessful()
            ->dump()
            ->assertJsonFragment($personalities->first()->toArray());
    }
}
