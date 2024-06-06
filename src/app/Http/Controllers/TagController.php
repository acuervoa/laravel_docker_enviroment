<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Video;
use App\Models\Channel;
use App\Models\VideoList;

class TagController extends Controller
{
    public function addTag(Request $request)
    {
        $tag = Tag::create($request->validate([
            'name' => 'required|string|max:255',
        ]));

        return response()->json($tag, 201);
    }

    public function attachTagToVideo(Request $request, $videoId)
    {
        return $this->attachTag($request, Video::class, $videoId);
    }

    public function attachTagToChannel(Request $request, $channelId)
    {
        return $this->attachTag($request, Channel::class, $channelId);
    }

    public function attachTagToList(Request $request, $listId)
    {
        return $this->attachTag($request, VideoList::class, $listId);
    }

    private function attachTag(Request $request, $modelClass, $modelId)
    {
        $model = $modelClass::findOrFail($modelId);
        $tag = Tag::findOrFail($request->input('tag_id'));

        $model->tags()->attach($tag);

        return response()->json(['message' => 'Tag attached to ' . class_basename($modelClass)]);
    }
}

