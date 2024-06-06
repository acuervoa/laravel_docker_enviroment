<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function videos()
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }

    public function channels()
    {
        return $this->morphedByMany(Channel::class, 'taggable');
    }

    public function lists()
    {
        return $this->morphedByMany(VideoList::class, 'taggable');
    }
}

