<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Video;
use App\Models\Channel;
use App\Models\User;
use App\Models\Subscription;

class WatchedHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_watched_history()
    {
        $channel = Channel::factory()->create();
        Subscription::factory()->create(['user_id' => $this->user->id, 'channel_id' => $channel->id]);

        $video1 = Video::factory()->create(['channel_id' => $channel->id, 'watched' => true, 'watched_at' => now()->subDays(2)]);
        $video2 = Video::factory()->create(['channel_id' => $channel->id, 'watched' => true, 'watched_at' => now()->subDay()]);
        $video3 = Video::factory()->create(['channel_id' => $channel->id, 'watched' => false]);

        $response = $this->get('/videos/watched-history');
        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['youtube_id' => $video1->youtube_id])
                 ->assertJsonFragment(['youtube_id' => $video2->youtube_id])
                 ->assertJsonMissing(['youtube_id' => $video3->youtube_id]);
    }
}

