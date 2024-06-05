<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'youtube_id', 'category', 'subscriber_count'];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function subscribers()
    {
        return $this->hasMany(Subscription::class);
    }

}
