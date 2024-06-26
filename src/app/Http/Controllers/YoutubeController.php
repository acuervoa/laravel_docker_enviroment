<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\Video;
use Carbon\Carbon;

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
        if (!$request->has('code')) {
            return redirect()->away($this->client->createAuthUrl());
        }

        $this->client->authenticate($request->input('code'));
        Storage::put('youtube_token.json', json_encode($this->client->getAccessToken()));
        return redirect()->route('youtube.channels');
    }

    public function getChannels()
    {
        $channels = [];
        $pageToken = null;

        do {
            $response = $this->youtube->subscriptions->listSubscriptions('snippet', [
                'mine' => true,
                'maxResults' => 50,
                'pageToken' => $pageToken,
            ]);

            foreach ($response->getItems() as $subscription) {
                $channelId = $subscription->getSnippet()->getResourceId()->getChannelId();
                $channelTitle = $subscription->getSnippet()->getTitle();

                $channels[] = [
                    'id' => $channelId,
                    'title' => $channelTitle,
                ];

                $this->updateOrCreateChannel($channelId, $channelTitle);
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return response()->json($channels);
    }

    private function updateOrCreateChannel($channelId, $channelTitle)
    {
        Channel::updateOrCreate(
            ['youtube_id' => $channelId],
            [
                'name' => $channelTitle,
                'category' => 'Uncategorized',
            ]
        );
    }

    public function getVideos(string $channelId)
    {
        $channel = Channel::where('youtube_id', $channelId)->firstOrFail();
        $existingVideoIds = $channel->videos->pluck('youtube_id')->toArray();

        $pageToken = null;
        do {
            $response = $this->youtube->search->listSearch('snippet', [
                'channelId' => $channelId,
                'maxResults' => 50,
                'order' => 'date',
                'pageToken' => $pageToken,
            ]);

            foreach ($response->getItems() as $video) {
                $this->updateOrCreateVideo($channel, $video, $existingVideoIds);
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return response()->json(['message' => 'Videos updated successfully']);
    }

    private function updateOrCreateVideo($channel, $video, $existingVideoIds)
    {
        $videoId = $video->getId()->getVideoId();
        if (!in_array($videoId, $existingVideoIds)) {
            $publishedAt = $video->getSnippet()->getPublishedAt();

            Video::updateOrCreate(
                ['youtube_id' => $videoId],
                [
                    'channel_id' => $channel->id,
                    'title' => $video->getSnippet()->getTitle(),
                    'published_at' => Carbon::parse($publishedAt)->format('Y-m-d H:i:s'),
                ]
            );

            $channel->update([
                'last_video_uploaded_at' => Carbon::parse($publishedAt)->format('Y-m-d H:i:s'),
                'unwatched_videos_count' => $channel->unwatched_videos_count + 1,
            ]);
        }
    }

    public function getChannelVideos($channelId)
    {
        $videos = Video::where('channel_id', $channelId)->get();
        return response()->json($videos);
    }

    public function markVideoAsWatched($videoId)
    {
        $video = Video::where('youtube_id', $videoId)->firstOrFail();
        if (!$video->watched) {
            $video->update(['watched' => true, 'watched_at' => now()]);

            $video->channel->update([
                'last_video_watched_at' => now(),
                'watched_videos_count' => $video->channel->watched_videos_count + 1,
                'unwatched_videos_count' => $video->channel->unwatched_videos_count - 1,
            ]);
        }

        return response()->json(['message' => 'Video marked as watched']);
    }

    public function getVideoCounts($channelId)
    {
        $channel = Channel::where('youtube_id', $channelId)->firstOrFail();
        return response()->json([
            'watched_videos_count' => $channel->watched_videos_count,
            'unwatched_videos_count' => $channel->unwatched_videos_count,
        ]);
    }
}

