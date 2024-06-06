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
        $tag = Tag::create($request->all());
        return response()->json($tag, 201);
    }

    public function attachTagToVideo(Request $request, $videoId)
    {
        $video = Video::find($videoId);
        $tag = Tag::find($request->input('tag_id'));
        $video->tags()->attach($tag);

        return response()->json(['message' => 'Tag attached to video']);
    }

    public function attachTagToChannel(Request $request, $channelId)
    {
        $channel = Channel::find($channelId);
        $tag = Tag::find($request->input('tag_id'));
        $channel->tags()->attach($tag);

        return response()->json(['message' => 'Tag attached to channel']);
    }

    public function attachTagToList(Request $request, $listId)
    {
        $list = VideoList::find($listId);
        $tag = Tag::find($request->input('tag_id'));
        $list->tags()->attach($tag);

        return response()->json(['message' => 'Tag attached to list']);
    }
}

