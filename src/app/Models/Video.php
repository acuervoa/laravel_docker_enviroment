<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'title', 'youtube_id', 'like_count', 'published_at', 'watched', 'rating'];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

}
