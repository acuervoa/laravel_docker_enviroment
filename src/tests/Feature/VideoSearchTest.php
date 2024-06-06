<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Video;
use App\Models\Channel;
use App\Models\User;

class VideoSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user); // Asegura autenticación
    }

    public function test_search_videos_by_title()
    {
        $channel = Channel::factory()->create();
        $video1 = Video::factory()->create(['title' => 'Test Video 1', 'channel_id' => $channel->id]);
        $video2 = Video::factory()->create(['title' => 'Another Video', 'channel_id' => $channel->id]);

        $response = $this->actingAs($this->user)->get('/videos/search?title=Test');
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }

    public function test_search_videos_by_channel()
    {
        $channel1 = Channel::factory()->create();
        $channel2 = Channel::factory()->create();
        $video1 = Video::factory()->create(['title' => 'Test Video 1', 'channel_id' => $channel1->id]);
        $video2 = Video::factory()->create(['title' => 'Another Video', 'channel_id' => $channel2->id]);

        $response = $this->actingAs($this->user)->get('/videos/search?channel_id=' . $channel1->id);
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }

    public function test_search_videos_by_date_range()
    {
        $channel = Channel::factory()->create();
        $video1 = Video::factory()->create(['title' => 'Test Video 1', 'channel_id' => $channel->id, 'published_at' => now()->subDays(5)]);
        $video2 = Video::factory()->create(['title' => 'Another Video', 'channel_id' => $channel->id, 'published_at' => now()->subDays(15)]);

        $response = $this->actingAs($this->user)->get('/videos/search?published_after=' . now()->subDays(10)->toDateString());
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }
}

