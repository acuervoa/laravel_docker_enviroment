<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Channel;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::with('channel')->get();
        return response()->json($videos);
    }

    public function show($id)
    {
        $video = Video::with('channel')->findOrFail($id);
        return response()->json($video);
    }

    public function store(Request $request)
    {
        $channel = Channel::where('youtube_id', $request->channel_youtube_id)->first();
        if (!$channel) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        $video = Video::create($this->getValidatedData($request, $channel->id));

        return response()->json($video, 201);
    }

    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $video->update($request->all());

        return response()->json($video);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return response()->json(null, 204);
    }

    public function getVideoDetails($videoId)
    {
        $video = Video::with('channel')->findOrFail($videoId);
        return response()->json($video);
    }

    public function likeVideo($videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->increment('likes');
        return response()->json(['message' => 'Video liked', 'likes' => $video->likes]);
    }

    public function dislikeVideo($videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->increment('dislikes');
        return response()->json(['message' => 'Video disliked', 'dislikes' => $video->dislikes]);
    }

    public function search(Request $request)
    {
        $query = Video::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->input('channel_id'));
        }

        if ($request->filled('published_after')) {
            $query->where('published_at', '>=', $request->input('published_after'));
        }

        if ($request->filled('published_before')) {
            $query->where('published_at', '<=', $request->input('published_before'));
        }

        $videos = $query->get();
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

    public function getWatchedHistory(Request $request)
    {
        $user = $request->user();
        $videos = Video::whereHas('channel.subscribers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereNotNull('watched_at')->orderBy('watched_at', 'desc')->get();

        return response()->json($videos);
    }

    private function getValidatedData(Request $request, $channelId)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'youtube_id' => 'required|string|max:255',
            'like_count' => 'integer|min:0',
            'published_at' => 'required|date',
            'watched' => 'boolean',
            'rating' => 'integer|min:1|max:5',
        ]) + ['channel_id' => $channelId];
    }
}

