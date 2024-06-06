<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'title',
        'description',
        'url',
        'youtube_id',
        'views',
        'likes',
        'dislikes',
        'like_count',
        'published_at',
        'watched',
        'rating',
        'watched_at'
    ];


    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
