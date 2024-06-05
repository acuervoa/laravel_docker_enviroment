<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\Video;

class YoutubeController extends Controller
{
    private $client;
    private $youtube;

    public function __construct()
    {
        $this->client = $this->getClient();
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    private function getClient()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
        $client->setRedirectUri(route('youtube.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        if (Storage::exists('youtube_token.json')) {
            $token = json_decode(Storage::get('youtube_token.json'), true);
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                Storage::put('youtube_token.json', json_encode($client->getAccessToken()));
            }
        }

        return $client;
    }

    public function authenticate(Request $request)
    {
        $client = $this->client;

        if (!$request->has('code')) {
            $authUrl = $client->createAuthUrl();
            return redirect()->away($authUrl);
        } else {
            $client->authenticate($request->input('code'));
            $token = $client->getAccessToken();
            Storage::put('youtube_token.json', json_encode($token));
            return redirect()->route('youtube.channels');
        }
    }

    public function getChannels()
    {
        $channelsResponse = $this->youtube->channels->listChannels('snippet,contentDetails,statistics', [
            'mine' => true,
        ]);

        foreach ($channelsResponse->getItems() as $channel) {
            Channel::updateOrCreate(
                ['youtube_id' => $channel->getId()],
                [
                    'name' => $channel->getSnippet()->getTitle(),
                    'category' => 'Uncategorized',
                ]
            );
        }

        return response()->json($channelsResponse);
    }

    public function getVideos($channelId)
    {
        $existingVideos = Video::where('channel_id', Channel::where('youtube_id', $channelId)->first()->id)->pluck('youtube_id')->toArray();

        $videosResponse = $this->youtube->search->listSearch('snippet', [
            'channelId' => $channelId,
            'maxResults' => 10,
            'order' => 'date',
        ]);

        foreach ($videosResponse->getItems() as $video) {
            $videoId = $video->getId()->getVideoId();
            if (!in_array($videoId, $existingVideos)) {
                Video::updateOrCreate(
                    ['youtube_id' => $videoId],
                    [
                        'channel_id' => Channel::where('youtube_id', $channelId)->first()->id,
                        'title' => $video->getSnippet()->getTitle(),
                    ]
                );
            }
        }

        return response()->json($videosResponse);
    }
}

