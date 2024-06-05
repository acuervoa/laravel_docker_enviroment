<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Channel;
use App\Models\Video;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_create_video()
    {
        $channel = Channel::factory()->create();

        $response = $this->post('/videos', [
            'channel_youtube_id' => $channel->youtube_id,
            'title' => 'Test Video',
            'youtube_id' => 'testYoutubeId',
            'like_count' => 100,
            'published_at' => now(),
            'watched' => false,
            'rating' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'channel_id', 'title', 'youtube_id', 'like_count', 'published_at', 'watched', 'rating']);

        $this->assertDatabaseHas('videos', [
            'title' => 'Test Video',
        ]);
    }

    public function test_user_can_view_videos()
    {
        $video = Video::factory()->create();

        $response = $this->get('/videos');

        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'channel_id', 'title', 'youtube_id', 'like_count', 'published_at', 'watched', 'rating']]);
    }

    public function test_user_can_view_single_video()
    {
        $video = Video::factory()->create();

        $response = $this->get("/videos/{$video->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'channel_id', 'title', 'youtube_id', 'like_count', 'published_at', 'watched', 'rating']);
    }

    public function test_user_can_update_video()
    {
        $video = Video::factory()->create();

        $response = $this->put("/videos/{$video->id}", [
            'title' => 'Updated Test Video',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Test Video']);

        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'title' => 'Updated Test Video',
        ]);
    }

    public function test_user_can_delete_video()
    {
        $video = Video::factory()->create();

        $response = $this->delete("/videos/{$video->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('videos', [
            'id' => $video->id,
        ]);
    }
}

