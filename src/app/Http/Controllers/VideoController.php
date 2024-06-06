<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Channel;

class VideoController extends Controller
{
    public function index(Request $request)
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

        $video = Video::create([
            'channel_id' => $channel->id,
            'title' => $request->title,
            'youtube_id' => $request->youtube_id,
            'like_count' => $request->like_count,
            'published_at' => $request->published_at,
            'watched' => $request->watched,
            'rating' => $request->rating,
        ]);

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

    public function getVideoDetails(Request $request, $videoId)
    {
        $video = Video::where('id', $videoId)->with('channel')->firstOrFail();

        return response()->json($video);
    }

    public function likeVideo(Request $request, $videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->likes += 1;
        $video->save();

        return response()->json(['message' => 'Video liked', 'likes' => $video->likes]);
    }

    public function dislikeVideo(Request $request, $videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->dislikes += 1;
        $video->save();

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
}

