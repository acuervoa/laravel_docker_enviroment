<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\Video;

class YouTubeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_authenticate_redirects_to_google()
    {
        $response = $this->get('/youtube/authenticate');
        $response->assertRedirect();
    }

    public function test_get_channels()
    {
        Storage::disk('local')->put('token.json', json_encode([
            'access_token' => 'valid-access-token',
            'expires_in' => 3600,
            'refresh_token' => 'valid-refresh-token',
            'created' => time()
        ]));

        $response = $this->get('/youtube/channels');
        $response->assertStatus(200);


    }

    public function test_get_videos()
    {
  /*      Storage::disk('local')->put('token.json', json_encode([
            'access_token' => 'valid-access-token',
            'expires_in' => 3600,
            'refresh_token' => 'valid-refresh-token',
            'created' => time()
        ]));
   */
 $channel = Channel::factory()->create(['youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw']);
        Storage::put('youtube_token.json', json_encode(['access_token' => 'test-token', 'expires_in' => 3600]));

        $this->get('/youtube/videos/UC_x5XG1OV2P6uZZ5FSM9Ttw')
             ->assertStatus(200);

        // Verifica que los videos se guardan en la base de datos
        $this->assertDatabaseHas('videos', [
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Android Development for Beginners'
        ]);
    }
}

