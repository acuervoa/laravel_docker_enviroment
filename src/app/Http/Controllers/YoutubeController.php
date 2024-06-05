<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_YouTube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\Channel;
use App\Models\Video;

class YouTubeController extends Controller
{
    protected $client;
    protected $youtube;

    public function __construct(Google_Client $client, Google_Service_YouTube $youtube)
    {
        $this->client = $client;
        $this->youtube = $youtube;
    }

    public function authenticate(Request $request)
    {
        try {
            $client = $this->getClient();

            if (!$request->has('code')) {
                $authUrl = $client->createAuthUrl();
                return redirect()->away($authUrl);
            } else {
                $code = $request->input('code');

                $accessToken = $client->fetchAccessTokenWithAuthCode($code);
                if (isset($accessToken['error'])) {
                    throw new \Exception('Error fetching access token: ' . $accessToken['error_description']);
                }

                $client->setAccessToken($accessToken);
                $token = $client->getAccessToken();

                if ($token === null) {
                    throw new \Exception('Failed to get access token.');
                }

                Storage::put('youtube_token.json', json_encode($token));
                return redirect()->route('youtube.channels');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getChannels()
    {
        try {
            $client = $this->getClient();
            if (Storage::exists('youtube_token.json')) {
                $tokenPath = Storage::get('youtube_token.json');
                $tokenData = json_decode($tokenPath, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON token: ' . json_last_error_msg());
                }


                $client->setAccessToken($tokenData);

                if ($client->isAccessTokenExpired()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $newToken = $client->getAccessToken();
                    Storage::put('youtube_token.json', json_encode($newToken));
                    $client->setAccessToken($newToken);
                }


                $youtube = new Google_Service_YouTube($client);
                $channelsResponse = $youtube->subscriptions->listSubscriptions('snippet', [
                    'mine' => true,
                    'maxResults' => 50
                ]);


                $channels = [];

                foreach ($channelsResponse->getItems() as $item) {
                    $snippet = $item->getSnippet();
                    $channel = Channel::updateOrCreate(
                        ['youtube_id' => $snippet->getResourceId()->getChannelId()],
                        [
                            'name' => $snippet->getTitle(),
                            'category' => 'Unknown',
                            'subscriber_count' => 0
                        ]
                    );

                    $channels[] = $channel;
                   # $videos = $this->getVideos($snippet->getResourceId()->getChannelId());
                }

                return response()->json($channels);
            } else {
                throw new \Exception('Token file does not exist');
            }
        } catch (\Exception $e) {
            Log::error('Fetching channels failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getVideos($channelId)
    {
        try {
            Log::info('Fetching videos for channel: ' . $channelId);
            $client = $this->getClient();
            if (Storage::exists('youtube_token.json')) {
                $tokenPath = Storage::get('youtube_token.json');
                $tokenData = json_decode($tokenPath, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON token: ' . json_last_error_msg());
                }

                $client->setAccessToken($tokenData);

                if ($client->isAccessTokenExpired()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $newToken = $client->getAccessToken();
                    Storage::put('youtube_token.json', json_encode($newToken));
                    $client->setAccessToken($newToken);
                }


                $youtube = new Google_Service_YouTube($client);
                $videosResponse = $youtube->search->listSearch('snippet', [
                    'channelId' => $channelId,
                    'maxResults' => 50,
                    'order' => 'date'
                ]);

                Log::info('Videos fetched successfully');

                $videos = [];
                foreach($videosResponse->getItems() as $item) {
                    $snippet = $item->getSnippet();
                    $video = Video::updateOrCreate(
                        ['youtube_id' => $item->getId()->getVideoId()],
                        [
                            'channel_id' => Channel::where('youtube_id', $channelId)->first()->id,
                            'title'=> $snippet->getTitle(),
                            'like_count'=>0,
                            'published_at' => date('Y-m-d H:i:s', strtotime($snippet->getPublishedAt())),
                            'watched' => false,
                            'rating' => null
                        ]
                    );
                    $videos[] = $video;
                }

                return response()->json($videos);
            } else {
                throw new \Exception('Token file does not exist');
            }
        } catch (\Exception $e) {
            Log::error('Fetching videos failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.youtube.client_id'));
        $client->setClientSecret(config('services.youtube.client_secret'));
        $client->setRedirectUri(config('services.youtube.redirect_uri'));
        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setDeveloperKey(config('services.youtube.developer_key'));

        Log::info('Client ID: ' . config('services.youtube.client_id'));
        Log::info('Client Secret: ' . config('services.youtube.client_secret'));
        Log::info('Redirect URI: ' . config('services.youtube.redirect_uri'));

        return $client;
    }
}

