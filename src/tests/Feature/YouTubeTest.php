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

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockGoogleClient();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function mockGoogleClient(): void
    {
        if (!class_exists('Google_Client', false)) {
            $mockClient = Mockery::mock('alias:Google_Client');
            $mockClient->shouldReceive('setAuthConfig')->andReturnSelf();
            $mockClient->shouldReceive('addScope')->andReturnSelf();
            $mockClient->shouldReceive('setRedirectUri')->andReturnSelf();
            $mockClient->shouldReceive('setAccessType')->andReturnSelf();
            $mockClient->shouldReceive('setPrompt')->andReturnSelf();
            $mockClient->shouldReceive('isAccessTokenExpired')->andReturn(false);
            $mockClient->shouldReceive('getAccessToken')->andReturn(['access_token' => 'fake_token']);
            $mockClient->shouldReceive('setAccessToken')->andReturnSelf();

            $mockYouTube = Mockery::mock('alias:Google_Service_YouTube');
            $mockYouTube->subscriptions = Mockery::mock();
            $mockYouTube->search = Mockery::mock();

            $mockYouTube->subscriptions->shouldReceive('listSubscriptions')->andReturn((object)[
                'items' => [
                    (object)[
                        'snippet' => (object)[
                            'resourceId' => (object)['channelId' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw'],
                            'title' => 'Test Channel'
                        ]
                    ]
                ],
                'nextPageToken' => null
            ]);

            $mockYouTube->search->shouldReceive('listSearch')->andReturn((object)[
                'items' => [
                    (object)[
                        'id' => (object)['videoId' => 'Ks-_Mh1QhMc'],
                        'snippet' => (object)['title' => 'Test Video']
                    ]
                ],
                'nextPageToken' => null
            ]);
        }
    }

    public function test_authenticate_redirects_to_google(): void
    {
        $response = $this->get('/youtube/authenticate');
        $response->assertRedirect();
    }

    public function test_get_channels_from_db(): void
    {

        $timestamp = now()->format('Y-m-d H:i:s');

        Channel::create([
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'category' => 'Test Category',
            'last_video_uploaded_at' => $timestamp,
        ]);
        var_dump($timestamp);

        $response = $this->get('/youtube/channels');
        $response->assertStatus(200);

        $this->assertDatabaseHas('channels', [
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'last_video_uploaded_at' => $timestamp,
        ]);
    }

    public function test_get_videos_from_db(): void
    {

        $timestamp = now()->format('Y-m-d H:i:s');

        $channel = Channel::create([
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'category' => 'Test Category',
            'last_video_uploaded_at' => $timestamp,
        ]);

        Video::create([
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Test Video',
            'channel_id' => $channel->id,
            'published_at' => $timestamp,
        ]);

        $response = $this->get('/youtube/videos/UC_x5XG1OV2P6uZZ5FSM9Ttw');
        $response->assertStatus(200);

        $this->assertDatabaseHas('videos', [
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Test Video'
        ]);

        $this->assertDatabaseHas('channels', [
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
             'last_video_uploaded_at' => $timestamp,
        ]);
    }

    public function test_mark_video_as_watched(): void
    {

        $timestamp = now();

        $channel = Channel::create([
            'youtube_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'name' => 'Test Channel',
            'category' => 'Test Category',
        ]);

        $video = Video::create([
            'youtube_id' => 'Ks-_Mh1QhMc',
            'title' => 'Test Video',
            'channel_id' => $channel->id,
            'published_at' => $timestamp,
        ]);

        $response = $this->post("/videos/{$video->youtube_id}/watch");
        $response->assertStatus(200);

        $this->assertDatabaseHas('videos', [
            'youtube_id' => 'Ks-_Mh1QhMc',
            'watched' => true,
        ]);

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'last_video_watched_at' => $timestamp,
        ]);
    }

}

