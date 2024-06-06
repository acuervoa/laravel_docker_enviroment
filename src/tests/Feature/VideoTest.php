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

        $response = $this->post('/videos', $this->getVideoData($channel->youtube_id));

        $response->assertStatus(201);
        $this->assertDatabaseHas('videos', [
            'title' => 'Test Video',
            'youtube_id' => 'Ks-_Mh1QhMc',
        ]);
    }

    public function test_user_can_view_videos()
    {
        $video = Video::factory()->create();

        $response = $this->get('/videos');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => $video->title,
                 ]);
    }

    public function test_user_can_view_single_video()
    {
        $video = Video::factory()->create();

        $response = $this->get('/videos/' . $video->id);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => $video->title,
                 ]);
    }

    public function test_user_can_update_video()
    {
        $video = Video::factory()->create();

        $response = $this->put('/videos/' . $video->id, [
            'title' => 'Updated Video Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'title' => 'Updated Video Title',
        ]);
    }

    public function test_user_can_delete_video()
    {
        $video = Video::factory()->create();

        $response = $this->delete('/videos/' . $video->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('videos', [
            'id' => $video->id,
        ]);
    }

    private function getVideoData($youtubeId)
    {
        return [
            'channel_youtube_id' => $youtubeId,
            'title' => 'Test Video',
            'youtube_id' => 'Ks-_Mh1QhMc',
            'like_count' => 100,
            'published_at' => now(),
            'watched' => false,
            'rating' => 5,
        ];
    }
}

