<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'youtube_id',
        'category',
        'subscriber_count',
        'last_video_uploaded_at',
        'last_video_watched_at'
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function subscribers()
    {
        return $this->hasMany(Subscription::class);
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
