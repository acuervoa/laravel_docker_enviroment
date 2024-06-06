<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tag;
use App\Models\Video;
use App\Models\Channel;
use App\Models\VideoList;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_tag()
    {
        $response = $this->postJson('/tags', ['name' => 'Test Tag']);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tags', ['name' => 'Test Tag']);
    }

    public function test_attach_tag_to_video()
    {
        $tag = Tag::create(['name' => 'Test Tag']);
        $video = Video::factory()->create();

        $response = $this->attachTag($tag, $video, 'video');
        $response->assertStatus(200);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $video->id,
            'taggable_type' => Video::class,
        ]);
    }

    public function test_attach_tag_to_channel()
    {
        $tag = Tag::create(['name' => 'Test Tag']);
        $channel = Channel::factory()->create();

        $response = $this->attachTag($tag, $channel, 'channel');
        $response->assertStatus(200);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $channel->id,
            'taggable_type' => Channel::class,
        ]);
    }

    public function test_attach_tag_to_list()
    {
        $tag = Tag::create(['name' => 'Test Tag']);
        $list = VideoList::factory()->create();

        $response = $this->attachTag($tag, $list, 'list');
        $response->assertStatus(200);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $list->id,
            'taggable_type' => VideoList::class,
        ]);
    }

    private function attachTag($tag, $model, $type)
    {
        return $this->postJson("/{$type}s/{$model->id}/tags", ['tag_id' => $tag->id]);
    }
}

