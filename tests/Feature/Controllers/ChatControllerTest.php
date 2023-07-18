<?php

namespace Tests\Feature\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function canGetConversations()
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
}
