<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Subscription;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_notify_new_videos()
    {
        $channel = Channel::factory()->create();
        $subscription = Subscription::create(['user_id' => $this->user->id, 'channel_id' => $channel->id]);
        $video = Video::factory()->create(['channel_id' => $channel->id, 'created_at' => now()]);

        $response = $this->get('/notify-new-videos');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Notifications sent']);
    }
}

