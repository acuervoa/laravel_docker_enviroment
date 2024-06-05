<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->post('/subscribe', [
            'youtube_id' => $channel->youtube_id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'channel_id' => $channel->id,
        ]);
    }

    public function test_user_can_unsubscribe_from_channel()
    {
        $channel = Channel::factory()->create();
        $subscription = Subscription::create(['user_id' => $this->user->id, 'channel_id' => $channel->id]);

        $response = $this->delete('/unsubscribe/' . $channel->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('subscriptions', [
            'user_id' => $this->user->id,
            'channel_id' => $channel->id,
        ]);
    }

    public function test_user_can_view_subscriptions()
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get('/subscriptions');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'channel_id' => $subscription->channel_id,
            ]);
    }
}

