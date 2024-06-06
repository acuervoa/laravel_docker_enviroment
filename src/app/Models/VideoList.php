<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoList extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

