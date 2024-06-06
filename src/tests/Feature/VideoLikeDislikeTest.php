<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Video;
use App\Models\User;

class VideoLikeDislikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_like_video()
    {
        $video = Video::factory()->create();

        $response = $this->postJson("/videos/{$video->id}/like");
        $response->assertStatus(200);
        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'likes' => 1,
        ]);
    }

    public function test_dislike_video()
    {
        $video = Video::factory()->create();

        $response = $this->postJson("/videos/{$video->id}/dislike");
        $response->assertStatus(200);
        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'dislikes' => 1,
        ]);
    }
}

