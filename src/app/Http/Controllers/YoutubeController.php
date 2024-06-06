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
        $this->client = $this->initializeGoogleClient();
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    private function initializeGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
        $client->setRedirectUri(route('youtube.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        if (Storage::exists('youtube_token.json')) {
            $this->setAccessToken($client);
        }

        return $client;
    }

    private function setAccessToken(Google_Client $client): void
    {
        $token = json_decode(Storage::get('youtube_token.json'), true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            Storage::put('youtube_token.json', json_encode($client->getAccessToken()));
        }
    }

    public function authenticate(Request $request)
    {
        $client = $this->client;

        if (!$request->has('code')) {
            return redirect()->away($client->createAuthUrl());
        }

        $client->authenticate($request->input('code'));
        Storage::put('youtube_token.json', json_encode($client->getAccessToken()));
        return redirect()->route('youtube.channels');
    }

    public function getChannels()
    {
        $channelsResponse = $this->youtube->channels->listChannels('snippet,contentDetails,statistics', [
            'mine' => true,
        ]);

        foreach ($channelsResponse->getItems() as $channel) {
            Channel::updateOrCreate(
                ['youtube_id' => $channel->getId()],
                ['name' => $channel->getSnippet()->getTitle(), 'category' => 'Uncategorized']
            );
        }

        return response()->json($channelsResponse);
    }

    public function getVideos(string $channelId)
    {
        $channel = Channel::where('youtube_id', $channelId)->firstOrFail();
        $existingVideoIds = $channel->videos->pluck('youtube_id')->toArray();

        $videosResponse = $this->youtube->search->listSearch('snippet', [
            'channelId' => $channelId,
            'maxResults' => 10,
            'order' => 'date',
        ]);

        foreach ($videosResponse->getItems() as $video) {
            $videoId = $video->getId()->getVideoId();
            if (!in_array($videoId, $existingVideoIds)) {
                Video::updateOrCreate(
                    ['youtube_id' => $videoId],
                    ['channel_id' => $channel->id, 'title' => $video->getSnippet()->getTitle()]
                );
            }
        }

        return response()->json($videosResponse);
    }
    public function getChannelVideos(Request $request, $channelId)
    {
        $videos = Video::where('channel_id', $channelId)->get();
        return response()->json($videos);
    }

}

