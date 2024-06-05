<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\User;
use App\Models\Video;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class YouTubeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockGoogleClient();
        // Crear y autenticar un usuario
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function mockGoogleClient()
    {
        // Ensure that the class Google_Client is not already loaded
        if (!class_exists('Google_Client', false)) {
            // Mock Google_Client
            $mockClient = Mockery::mock('alias:Google_Client');
            $mockClient->shouldReceive('setAuthConfig')
                ->andReturnSelf();
            $mockClient->shouldReceive('addScope')
                ->andReturnSelf();
            $mockClient->shouldReceive('setRedirectUri')
                ->andReturnSelf();
            $mockClient->shouldReceive('setAccessType')
                ->andReturnSelf();
            $mockClient->shouldReceive('setPrompt')
                ->andReturnSelf();
            $mockClient->shouldReceive('isAccessTokenExpired')
                ->andReturn(false);
            $mockClient->shouldReceive('getAccessToken')
                ->andReturn(['access_token' => 'fake_token']);
            $mockClient->shouldReceive('setAccessToken')
                ->andReturnSelf();

            // Mock Google_Service_YouTube
            $mockYouTube = Mockery::mock('alias:Google_Service_YouTube');
            $mockYouTube->channels = Mockery::mock();
            $mockYouTube->search = Mockery::mock();

            $mockYouTube->channels->shouldReceive('listChannels')
                ->andReturn((object)['items' => [
                    (object)['id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', 'snippet' => (object)['title' => 'Test Channel']]
                ]]);

            $mockYouTube->search->shouldReceive('listSearch')
                ->andReturn((object)['items' => [
                    (object)['id' => (object)['videoId' => 'Ks-_Mh1QhMc'], 'snippet' => (object)['title' => 'Test Video']]
                ]]);
        }
    }

    public function test_authenticate_redirects_to_google()
    {
        $response = $this->get('/youtube/authenticate');
        $response->assertRedirect();
    }

    public function test_get_channels_from_db()
    {
        Channel::create([
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'category' => 'Test Category'
        ]);

        $response = $this->get('/youtube/channels');
        $response->assertStatus(200);

        $this->assertDatabaseHas('channels', [
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel'
        ]);
    }

    public function test_get_videos_from_db()
    {
        $channel = Channel::create([
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'category' => 'Test Category'
        ]);

        Video::create([
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Test Video',
            'channel_id' => $channel->id
        ]);

        $response = $this->get('/youtube/videos/UC_x5XG1OV2P6uZZ5FSM9Ttw');
        $response->assertStatus(200);

        $this->assertDatabaseHas('videos', [
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Test Video'
        ]);
    }
}

