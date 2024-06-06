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
        $this->createVideo($channel, 'Test Video 1');
        $this->createVideo($channel, 'Another Video');

        $response = $this->get('/videos/search?title=Test');
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }

    public function test_search_videos_by_channel()
    {
        $channel1 = Channel::factory()->create();
        $channel2 = Channel::factory()->create();
        $this->createVideo($channel1, 'Test Video 1');
        $this->createVideo($channel2, 'Another Video');

        $response = $this->get('/videos/search?channel_id=' . $channel1->id);
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }

    public function test_search_videos_by_date_range()
    {
        $channel = Channel::factory()->create();
        $this->createVideo($channel, 'Test Video 1', now()->subDays(5));
        $this->createVideo($channel, 'Another Video', now()->subDays(15));

        $response = $this->get('/videos/search?published_after=' . now()->subDays(10)->toDateString());
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Video 1'])
                 ->assertJsonMissing(['title' => 'Another Video']);
    }

    private function createVideo($channel, $title, $publishedAt = null)
    {
        return Video::factory()->create([
            'channel_id' => $channel->id,
            'title' => $title,
            'published_at' => $publishedAt ?? now(),
        ]);
    }
}

