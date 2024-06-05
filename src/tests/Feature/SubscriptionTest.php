<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Channel;
use App\Models\Subscription;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_subscribe_to_channel()
    {
        $channel = Channel::factory()->create();

        $response = $this->post('/subscribe', ['youtube_id' => $channel->youtube_id]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'channel_id' => $channel->id,
        ]);
    }

    public function test_user_can_unsubscribe_from_channel()
    {
        $user = User::factory()->create();
        $channel = Channel::factory()->create();
        $subscription = Subscription::create(['user_id' => $user->id,'channel_id' => $channel->id]);

        $this->actingAs($user)
            ->delete("/unsubscribe/{$channel->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('subscriptions', [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
        ]);

    }

    public function test_user_can_view_subscriptions()
    {
        $user= User::factory()->create();
        $channel = Channel::factory()->create();
        $subscription =Subscription::create(['user_id' => $user->id, 'channel_id' => $channel->id]);

        $this->actingAs($user)
             ->get('/subscriptions')
             ->assertStatus(200)
         ->assertJsonStructure([['id', 'channel_id', 'user_id', 'created_at', 'updated_at']]);
    }
}
